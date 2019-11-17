<?php declare(strict_types=1);

namespace TaskMachine\Task;

use Workflux\State\StateInterface;

final class Task implements StateInterface
{
    use TaskTrait;
}
