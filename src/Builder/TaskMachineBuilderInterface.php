<?php declare(strict_types=1);

namespace TaskMachine\Builder;

use TaskMachine\Handler\TaskHandlerInterface;
use TaskMachine\TaskMachineInterface;

interface TaskMachineBuilderInterface
{
    /** @param string|callable|TaskHandlerInterface $handler */
    public function task(string $name, $handler): TaskBuilder;

    public function machine(string $name): MachineBuilder;

    public function build(): TaskMachineInterface;

    public function merge(TaskMachineBuilderInterface $builder): TaskMachineBuilderInterface;
}
