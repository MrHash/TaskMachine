<?php

namespace TaskFlux;

use Auryn\Injector;
use Shrink0r\PhpSchema\Error;
use Workflux\Builder\Factory;
use Workflux\Builder\FactoryInterface;
use Workflux\Builder\StateMachineBuilder;
use Workflux\Builder\StateMachineSchema;
use Workflux\Error\ConfigError;
use Workflux\Param\Input;
use Workflux\Param\OutputInterface;
use Workflux\StateMachine;

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
        $this->tasks[$name] = $handler;
    }

    public function machine($name)
    {
        $schema = new StateMachineSchema;
        $this->machines[$name] = (new MachineBuilder($schema))->name($name);
        return $this->machines[$name];
    }

    public function run($name, array $params = []): OutputInterface
    {
        $result = $this->machines[$name]->build();
        if ($result instanceof Error) {
            throw new ConfigError('Invalid statemachine configuration given: '.print_r($result->unwrap(), true));
        }
        list($states, $transitions) = $this->realizeConfig($result->unwrap()['states']);
        return (new StateMachineBuilder(StateMachine::CLASS))
            ->addStateMachineName($name)
            ->addStates($states)
            ->addTransitions($transitions)
            ->build()
            ->execute(new Input($params));
    }

    private function getTaskHandler($name)
    {
        $handler = $this->tasks[$name];
        if ($handler instanceof \Closure) {
            return new ClosureHandler($name, $this->tasks[$name], $this->injector);
        } else {
            return $this->injector->make($handler);
        }
    }

    private function realizeConfig(array $config): array
    {
        $states = [];
        $transitions = [];
        foreach ($config as $name => $state_config) {

            // hacks
            $state_config['class'] = Task::class;
            if (isset($state_config['initial'])) {
                $state_config['settings'] = [ 'initial' => $state_config['initial'] ];
            }
            if (isset($state_config['final'])) {
                $state_config['settings'] = [ 'final' => $state_config['final'] ];
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