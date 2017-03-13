<?php

namespace TaskMachine\Handler;

use Workflux\Param\InputInterface;

interface TaskHandlerInterface
{
    public function execute(InputInterface $input): array;
}
