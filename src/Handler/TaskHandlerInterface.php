<?php declare(strict_types=1);

namespace TaskMachine\Handler;

use Workflux\Param\InputInterface;

interface TaskHandlerInterface
{
    public function execute(InputInterface $input): array;
}
