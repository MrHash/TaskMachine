<?php

namespace TaskMachine\Handler;

use Workflux\Param\InputInterface;

interface HandlerInterface
{
    public function execute(InputInterface $input): array;
}
