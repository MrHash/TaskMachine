<?php declare(strict_types=1);

namespace TaskMachine;

use Workflux\Param\OutputInterface;

interface TaskMachineInterface
{
    public function run(string $name, array $params = []): OutputInterface;
}
