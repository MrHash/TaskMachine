<?php

namespace TaskFlux\Task;

use Workflux\State\StateTrait;
use Workflux\Param\InputInterface;
use Workflux\State\StateInterface;

final class FinalTask implements StateInterface
{
    private $handler;

    use StateTrait;

    private function generateOutputParams(InputInterface $input): array
    {
        return $this->handler->execute($input);
    }

    public function isFinal(): bool
    {
        return true;
    }

    public function setHandler($handler)
    {
        $this->handler = $handler;
    }
}
