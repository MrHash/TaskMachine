<?php

namespace TaskFlux;

use Auryn\Injector;
use Shrink0r\PhpSchema\Error;
use TaskFlux\Handler\ClosureHandler;
use Workflux\Builder\Factory;
use Workflux\Builder\FactoryInterface;
use TaskFlux\Builder\MachineBuilder;
use Workflux\Builder\StateMachineBuilder;
use Workflux\Builder\StateMachineSchema;
use TaskFlux\Builder\TaskBuilder;
use Workflux\Error\ConfigError;
use Workflux\Param\Input;
use Workflux\Param\OutputInterface;
use TaskFlux\Schema\TaskSchema;
use Workflux\StateMachine;
use TaskFlux\Task\FinalTask;
use TaskFlux\Task\InitialTask;
use TaskFlux\Task\Task;

class TaskFlux
{
    private $injector;

    private $factory;

    private $tasks = [];

    private $machines = [];

    public function __construct(Injector $injector = null, FactoryInterface $factory = null)
    {
        $this->injector = $injector ?? new Injector;
        $this->factory = $factory ?? new Factory;
    }

    public function task($name, $handler)
    {
        $this->tasks[$name] = [
            'builder' => new TaskBuilder(new TaskSchema),
            'handler' => $handler
        ];
        return $this->tasks[$name]['builder'];
    }

    public function machine($name)
    {
        $this->machines[$name] = (new MachineBuilder(new StateMachineSchema))->name($name);
        return $this->machines[$name];
    }

    public function run($name, array $params = []): OutputInterface
    {
        $config = $this->build($name);
        list($states, $transitions) = $this->realizeConfig($config);
        return (new StateMachineBuilder(StateMachine::CLASS))
            ->addStateMachineName($name)
            ->addStates($states)
            ->addTransitions($transitions)
            ->build()
            ->execute(new Input($params));
    }

    private function build($machine)
    {
        $result = $this->machines[$machine]->build();
        if ($result instanceof Error) {
            throw new ConfigError('Invalid statemachine configuration given: '.print_r($result->unwrap(), true));
        }

        // haven't worked out how to merge the builders so tasks are merged here for now
        $schema = $result->unwrap()['states'];
        foreach ($schema as $task => $config) {
            if (!isset($this->tasks[$task])) {
                throw new ConfigError("Task $task has not been defined.");
            }

            $result = $this->tasks[$task]['builder']->build();
            if ($result instanceof Error) {
                throw new ConfigError('Invalid task configuration given: '.print_r($result->unwrap(), true));
            }

            $schema[$task] = array_merge($config, $result->unwrap());
        }
        // **

        return $schema;
    }

    private function getTaskHandler($name)
    {
        $handler = $this->tasks[$name]['handler'];
        if ($handler instanceof \Closure) {
            return new ClosureHandler($handler, $this->injector);
        } else {
            return $this->injector->make($handler);
        }
    }

    // would be nice to have an ArrayStateMachineBuilder in workflux
    private function realizeConfig(array $config): array
    {
        $states = [];
        $transitions = [];
        foreach ($config as $name => $state_config) {
            // hacks
            $state_config['class'] = Task::class;
            if (isset($state_config['initial'])) {
                $state_config['class'] = InitialTask::class;
            }
            if (isset($state_config['final'])) {
                $state_config['class'] = FinalTask::class;
            }
            // end hacks

            $state = $this->factory->createState($name, $state_config);
            if (!is_array($state_config)) {
                continue;
            }

            // hacks
            $state->setHandler($this->getTaskHandler($name));
            // end hacks

            $states[] = $state;
            foreach ($state_config['transitions'] as $key => $transition_config) {
                if (is_string($transition_config)) {
                    $transition_config = [ 'when' => $transition_config ];
                }
                $transitions[] = $this->factory->createTransition($name, $key, $transition_config);
            }
        }
        return [ $states, $transitions ];
    }
}
