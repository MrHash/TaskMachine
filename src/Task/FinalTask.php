<?php

namespace TaskFlux\Task;

use Workflux\State\StateTrait;
use Workflux\Param\InputInterface;
use Workflux\Param\OutputInterface;
use Workflux\State\StateInterface;

final class FinalTask implements StateInterface
{
    private $handler;

    use StateTrait;

    public function execute(InputInterface $input): OutputInterface
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
