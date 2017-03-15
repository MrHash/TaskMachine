<?php

namespace TaskMachine;

use Shrink0r\PhpSchema\Error;
use TaskMachine\Builder\MachineBuilder;
use TaskMachine\Builder\TaskBuilder;
use TaskMachine\Builder\TaskFactory;
use TaskMachine\Schema\MachineSchema;
use TaskMachine\Schema\TaskSchema;
use Workflux\Builder\ArrayStateMachineBuilder;
use Workflux\Builder\FactoryInterface;
use Workflux\Error\ConfigError;
use Workflux\Param\Input;
use Workflux\Param\OutputInterface;

class TaskMachine
{
    private $factory;

    private $tasks = [];

    private $handlers = [];

    private $machines = [];

    public function __construct(FactoryInterface $factory = null)
    {
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
            $taskConfig['settings']['_handler'] = $this->handlers[$task];
            $schema['states'][$task] = array_replace_recursive($config, $taskConfig);
        }
        // **

        $this->schema[$schema['name']] = $schema;
        return $this;
    }

    public function run($name, array $params = []): OutputInterface
    {
        return (new ArrayStateMachineBuilder($this->schema[$name], $this->factory))
            ->build()
            ->execute(new Input($params));
    }
}
