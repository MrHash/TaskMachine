<?php

namespace TaskMachine;

use Auryn\Injector;
use Shrink0r\PhpSchema\Error;
use TaskMachine\Builder\MachineBuilder;
use TaskMachine\Builder\TaskBuilder;
use TaskMachine\Builder\TaskFactory;
use TaskMachine\Handler\CallableTaskHandler;
use TaskMachine\Handler\TaskHandlerInterface;
use TaskMachine\Schema\MachineSchema;
use TaskMachine\Schema\TaskSchema;
use Workflux\Builder\FactoryInterface;
use Workflux\Builder\StateMachineBuilder;
use Workflux\Error\ConfigError;
use Workflux\Param\Input;
use Workflux\Param\OutputInterface;
use Workflux\StateMachine;

class TaskMachine
{
    private $injector;

    private $factory;

    private $tasks = [];

    private $handlers = [];

    private $machines = [];

    public function __construct(Injector $injector = null, FactoryInterface $factory = null)
    {
        $this->injector = $injector ?? new Injector;
        $this->factory = $factory ?? new TaskFactory;
    }

    public function task($name, $handler): TaskBuilder
    {
        $this->tasks[$name] = new TaskBuilder(new TaskSchema);
        $this->handlers[$name] = $handler;
        return $this->tasks[$name];
    }

    public function machine($name): MachineBuilder
    {
        $this->machines[$name] = (new MachineBuilder($this, new MachineSchema))->name($name);
        return $this->machines[$name];
    }

    public function build($name, array $defaults = []): TaskMachine
    {
        $result = $this->machines[$name]->buildConfig($defaults);

        if ($result instanceof Error) {
            throw new ConfigError('Invalid taskmachine configuration given: '.print_r($result->unwrap(), true));
        }

        // haven't worked out how to merge the builders so tasks are merged here for now
        $schema = $result->unwrap();
        foreach ($schema['states'] as $task => $config) {
            if (!isset($this->tasks[$task])) {
                throw new ConfigError("Task '$task' has not been defined.");
            }

            $result = $this->tasks[$task]->build();
            if ($result instanceof Error) {
                throw new ConfigError('Invalid task configuration given: '.print_r($result->unwrap(), true));
            }

            $taskConfig = $result->unwrap();
            $schema['states'][$task] = array_replace_recursive($config, $taskConfig);
        }
        // **

        $this->schema[$schema['name']] = $schema;
        return $this;
    }

    public function run($name, array $params = []): OutputInterface
    {
        list($states, $transitions) = $this->realizeConfig($this->schema[$name]['states']);
        return (new StateMachineBuilder(StateMachine::CLASS))
            ->addStateMachineName($this->schema[$name]['name'])
            ->addStates($states)
            ->addTransitions($transitions)
            ->build()
            ->execute(new Input($params));
    }

    private function realizeConfig(array $config): array
    {
        $states = [];
        $transitions = [];
        foreach ($config as $name => $state_config) {
            // build handler here
            $state_config['settings']['_handler'] = $this->realizeHandler($name);
            $states[] = $this->factory->createState($name, $state_config);
            if (!is_array($state_config)) {
                continue;
            }
            foreach ($state_config['transitions'] as $key => $transition_config) {
                if (is_string($transition_config)) {
                    $transition_config = [ 'when' => $transition_config ];
                }
                $transitions[] = $this->factory->createTransition($name, $key, $transition_config);
            }
        }
        return [$states, $transitions];
    }

    private function realizeHandler($name)
    {
        $handler = $this->handlers[$name];
        if (is_string($handler) && class_exists($handler)) {
            return $this->injector->make($handler);
        } elseif ($handler instanceof TaskHandlerInterface) {
            return $handler;
        } else {
            return new CallableTaskHandler($handler, $this->injector);
        }
    }
}
