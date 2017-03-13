<?php

namespace TaskMachine\Task;

use Workflux\State\StateInterface;

final class InteractiveTask implements StateInterface
{
    use TaskTrait;

    public function isInteractive(): bool
    {
        return true;
    }
}
