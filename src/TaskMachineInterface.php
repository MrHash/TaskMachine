<?php

namespace TaskMachine;

use Workflux\Param\OutputInterface;

interface TaskMachineInterface
{
    public function run(string $name, array $params = []): OutputInterface;
}
