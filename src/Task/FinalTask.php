<?php

namespace TaskMachine\Task;

use Workflux\State\StateInterface;

final class FinalTask implements StateInterface
{
    use TaskTrait;

    public function isFinal(): bool
    {
        return true;
    }
}
