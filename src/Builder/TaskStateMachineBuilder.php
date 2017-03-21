<?php

namespace TaskMachine\Builder;

use Shrink0r\Monatic\Maybe;
use Shrink0r\PhpSchema\Error;
use TaskMachine\Schema\MachineSchema;
use Workflux\Builder\Factory;
use Workflux\Builder\FactoryInterface;
use Workflux\Builder\StateMachineBuilder;
use Workflux\Builder\StateMachineBuilderInterface;
use Workflux\Error\ConfigError;
use Workflux\StateMachine;
use Workflux\StateMachineInterface;

class TaskStateMachineBuilder implements StateMachineBuilderInterface
{
    /**
     * @var string $name
     */
    private $name;

    /**
     * @var mixed[] $config
     */
    private $config;

    /**
     * @var FactoryInterface $factory
     */
    private $factory;

    /**
     * @param array $config
     * @param FactoryInterface|null $factory
     */
    public function __construct(string $name, array $config, FactoryInterface $factory = null)
    {
        $this->name = $name;
        $this->config = $config;
        $this->factory = $factory ?? new Factory;
    }

    /**
     * @return StateMachineInterface
     */
    public function build(): StateMachineInterface
    {
        $result = (new MachineSchema)->validate($this->config);
        if ($result instanceof Error) {
            throw new ConfigError('Invalid taskmachine configuration given: '.print_r($result->unwrap(), true));
        }
        list($tasks, $transitions) = $this->realizeConfig($this->config);
        $state_machine_class = Maybe::unit($this->config)->class->get() ?? StateMachine::CLASS;
        return (new StateMachineBuilder($state_machine_class))
            ->addStateMachineName($this->name)
            ->addStates($tasks)
            ->addTransitions($transitions)
            ->build();
    }

    /**
     * @param  mixed[] $config
     *
     * @return mixed[]
     */
    private function realizeConfig(array $config): array
    {
        $tasks = [];
        $transitions = [];
        foreach ($config as $name => $state_config) {
            $tasks[] = $this->factory->createState($name, $state_config);
            if (!is_array($state_config)) {
                continue;
            }
            foreach ((array)($state_config['transition'] ?? []) as $transition_config => $key) {
                if (is_string($transition_config)) {
                    $transition_config = [ 'when' => $transition_config ];
                }
                $transitions[] = $this->factory->createTransition($name, $key, $transition_config ?: null);
            }
        }
        return [ $tasks, $transitions ];
    }
}
