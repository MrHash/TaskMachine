<?php

namespace TaskMachine\Builder;

use TaskMachine\TaskMachineInterface;

interface TaskMachineBuilderInterface
{
    public function task(string $name, $handler): TaskBuilder;

    public function machine(string $name): MachineBuilder;

    public function build(): TaskMachineInterface;
}
