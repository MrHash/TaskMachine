<?php declare(strict_types=1);

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
    /** @var string */
    private $name;

    /** @var array */
    private $config;

    /** @var FactoryInterface */
    private $factory;

    public function __construct(string $name, array $config, FactoryInterface $factory = null)
    {
        $this->name = $name;
        $this->config = $config;
        $this->factory = $factory ?? new Factory;
    }

    public function build(): StateMachineInterface
    {
        $result = (new MachineSchema)->validate($this->config);
        if ($result instanceof Error) {
            throw new ConfigError('Invalid taskmachine configuration given: '.print_r($result->unwrap(), true));
        }
        list($tasks, $transitions) = $this->realizeConfig($this->config);
        $stateMachineClass = Maybe::unit($this->config)->class->get() ?? StateMachine::class;
        return (new StateMachineBuilder($stateMachineClass))
            ->addStateMachineName($this->name)
            ->addStates($tasks)
            ->addTransitions($transitions)
            ->build();
    }

    private function realizeConfig(array $config): array
    {
        $tasks = [];
        $transitions = [];
        foreach ($config as $name => $stateConfig) {
            $tasks[] = $this->factory->createState($name, $stateConfig);
            if (!is_array($stateConfig)) {
                continue;
            }
            foreach ((array)($stateConfig['transition'] ?? []) as $transitionConfig => $to) {
                if (is_string($transitionConfig)) {
                    $transitionConfig = ['when' => $transitionConfig];
                }
                $transitions[] = $this->factory->createTransition($name, $to, $transitionConfig ?: null);
            }
        }
        return [$tasks, $transitions];
    }
}
