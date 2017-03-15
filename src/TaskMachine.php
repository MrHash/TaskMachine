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

    private $builds = [];

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
        // build machine
        $result = $this->machines[$name]->buildConfig($defaults);

        if ($result instanceof Error) {
            throw new ConfigError('Invalid taskmachine configuration given: '.print_r($result->unwrap(), true));
        }

        // add handler implementor to config
        $schema = $result->unwrap();
        foreach ($schema['states'] as $task => $config) {
            $schema['states'][$task]['settings']['_handler'] = $this->handlers[$task];
        }
        $this->builds[$name] = $schema;

        return $this;
    }

    public function run($name, array $params = []): OutputInterface
    {
        return (new ArrayStateMachineBuilder($this->builds[$name], $this->factory))
            ->build()
            ->execute(new Input($params));
    }
}
