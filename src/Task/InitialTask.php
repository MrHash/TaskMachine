<?php declare(strict_types=1);

namespace TaskMachine\Task;

use Workflux\State\StateInterface;

final class InitialTask implements StateInterface
{
    use TaskTrait;

    public function isInitial(): bool
    {
        return true;
    }
}
