<?php

namespace TaskMachine;

use TaskMachine\Builder\ArrayTaskMachineBuilder;
use TaskMachine\Builder\TaskFactory;
use Workflux\Builder\FactoryInterface;
use Workflux\Param\Input;
use Workflux\Param\OutputInterface;

class TaskMachine
{
    private $factory;

    private $schemas;

    public function __construct(FactoryInterface $factory = null, array $schemas = [])
    {
        $this->factory = $factory ?? new TaskFactory;
        $this->schemas = $schemas;
    }

    public function run(string $name, array $params = []): OutputInterface
    {
        if (!isset($this->schemas[$name])) {
            throw new \RuntimeException("Machine '$name' not found");
        }

        return (new ArrayTaskMachineBuilder($name, $this->schemas[$name], $this->factory))
            ->build()
            ->execute(new Input($params));
    }
}
