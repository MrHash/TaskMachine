<?php

namespace TaskMachine\Task;

use Workflux\State\StateInterface;

final class Task implements StateInterface
{
    use TaskTrait;
}
