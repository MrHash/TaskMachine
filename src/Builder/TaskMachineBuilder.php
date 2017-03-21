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

    private $handlers = [];

    private $machines = [];

    public function __construct(FactoryInterface $factory = null)
    {
        $this->factory = $factory ?? new TaskFactory;
    }

    public function task(string $name, $handler): TaskBuilder
    {
        $this->tasks[$name] = new TaskBuilder(new TaskSchema);
        $this->handlers[$name] = $handler;
        return $this->tasks[$name];
    }

    public function machine(string $name): MachineBuilder
    {
        $this->machines[$name] = (new MachineBuilder($this, new MachineSchema));
        return $this->machines[$name];
    }

    public function build(array $defaults = []): TaskMachineInterface
    {
        $schemas = $this->buildMachines($defaults);
        return new TaskMachine($schemas, $this->factory);
    }

    protected function buildMachines(array $defaults = []): array
    {
        foreach ($this->machines as $name => $builder) {
            $result = $builder->buildConfig($defaults);

            if ($result instanceof Error) {
                throw new ConfigError('Invalid taskmachine configuration given: '.print_r($result->unwrap(), true));
            }

            $schema = $result->unwrap();
            $schemas[$name] = $this->mergeMachineTasks($schema);
        }

        return $schemas ?? [];
    }

    private function mergeMachineTasks(array $schema): array
    {
        foreach ($schema as $task => $config) {
            if (!isset($this->tasks[$task])) {
                throw new ConfigError("Task definition for '$task' not found");
            }

            $result = $this->tasks[$task]->build();

            if ($result instanceof Error) {
                throw new ConfigError('Invalid task configuration given: '.print_r($result->unwrap(), true));
            }

            $schema[$task] = array_replace_recursive($schema[$task], $result->unwrap());
            $schema[$task]['handler'] = $this->handlers[$task];
        }

        return $schema;
    }
}
