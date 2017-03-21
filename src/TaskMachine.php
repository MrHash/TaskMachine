<?php

namespace TaskMachine;

use TaskMachine\Builder\TaskFactory;
use TaskMachine\Builder\TaskStateMachineBuilder;
use Workflux\Builder\FactoryInterface;
use Workflux\Param\Input;
use Workflux\Param\OutputInterface;

class TaskMachine implements TaskMachineInterface
{
    private $schemas;

    private $factory;

    public function __construct(array $schemas, FactoryInterface $factory = null)
    {
        $this->schemas = $schemas;
        $this->factory = $factory ?? new TaskFactory;
    }

    public function run(string $name, array $params = []): OutputInterface
    {
        if (!isset($this->schemas[$name])) {
            throw new \RuntimeException("Machine '$name' not found");
        }

        return (new TaskStateMachineBuilder($name, $this->schemas[$name], $this->factory))
            ->build()
            ->execute(new Input($params));
    }
}
