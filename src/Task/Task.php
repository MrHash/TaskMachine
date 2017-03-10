<?php

namespace TaskMachine\Task;

use Workflux\State\StateTrait;
use Workflux\State\StateInterface;
use Workflux\Param\InputInterface;

final class Task implements StateInterface
{
    private $handler;

    use StateTrait;

    private function generateOutputParams(InputInterface $input): array
    {
        return $this->handler->execute($input);
    }

    public function setHandler($handler)
    {
        $this->handler = $handler;
    }
}
