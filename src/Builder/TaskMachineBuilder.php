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

    protected $tasks = [];

    protected $machines = [];

    public function __construct(FactoryInterface $factory = null)
    {
        $this->factory = $factory ?? new TaskFactory;
    }

    public function task(string $name, $handler): TaskBuilder
    {
        $this->tasks[$name] = new TaskBuilder($this, new TaskSchema);
        $this->tasks[$name]->handler($handler);
        return $this->tasks[$name];
    }

    public function getTasks(): array
    {
        return $this->tasks;
    }

    public function machine(string $name): MachineBuilder
    {
        $this->machines[$name] = (new MachineBuilder($this, new MachineSchema));
        return $this->machines[$name];
    }

    public function getMachines(): array
    {
        return $this->machines;
    }

    public function merge(TaskMachineBuilderInterface $builder): TaskMachineBuilderInterface
    {
        $this->tasks = array_merge($this->tasks, $builder->getTasks());
        $this->machines = array_merge($this->machines, $builder->getMachines());

        return $this;
    }

    public function build(): TaskMachineInterface
    {
        foreach ($this->machines as $name => $config) {
            if ($config instanceof MachineBuilder) {
                $result = $config->_build();
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
                $result = $this->tasks[$name]->_build();
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
