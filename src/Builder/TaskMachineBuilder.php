<?php

namespace TaskMachine\Builder;

use Shrink0r\PhpSchema\Error;
use TaskMachine\Schema\MachineSchema;
use TaskMachine\Schema\TaskSchema;
use TaskMachine\TaskMachine;
use TaskMachine\TaskMachineInterface;
use Workflux\Builder\FactoryInterface;
use Workflux\Error\ConfigError;

class TaskMachineBuilder implements TaskMachineBuilderInterface
{
    protected $factory;

    private $tasks = [];

    private $machines = [];

    public function __construct(FactoryInterface $factory = null)
    {
        $this->factory = $factory ?? new TaskFactory;
    }

    public function task(string $name, $handler): TaskBuilder
    {
        $this->tasks[$name] = new TaskBuilder(new TaskSchema);
        $this->tasks[$name]->handler($handler);
        return $this->tasks[$name];
    }

    protected function addTask(string $name, array $config)
    {
        // @todo handle validation
        $this->tasks[$name] = $config;
    }

    public function machine(string $name): MachineBuilder
    {
        $this->machines[$name] = (new MachineBuilder($this, new MachineSchema));
        return $this->machines[$name];
    }

    protected function addMachine(string $name, array $config)
    {
        // @todo handle validation
        $this->machines[$name] = $config;
    }

    public function build(array $defaults = []): TaskMachineInterface
    {
        foreach ($this->machines as $name => $config) {
            if ($config instanceof MachineBuilder) {
                $result = $config->buildConfig($defaults);
                if ($result instanceof Error) {
                    throw new ConfigError('Invalid taskmachine configuration given: '.print_r($result->unwrap(), true));
                }
                $config = $result->unwrap();
            }
            $schemas[$name] = $this->mergeMachineTasks($config);
        }

        return new TaskMachine($schemas ?? [], $this->factory);
    }

    private function mergeMachineTasks(array $schema): array
    {
        foreach ($schema as $name => $config) {
            if (!isset($this->tasks[$name])) {
                throw new ConfigError("Task definition for '$name' not found");
            }

            $taskConfig = $this->tasks[$name];
            if ($taskConfig instanceof TaskBuilder) {
                $result = $this->tasks[$name]->build();
                if ($result instanceof Error) {
                    throw new ConfigError('Invalid task configuration given: '.print_r($result->unwrap(), true));
                }
                $taskConfig = $result->unwrap();
            }

            $schema[$name] = array_replace_recursive($schema[$name], $taskConfig);
        }

        return $schema;
    }
}
