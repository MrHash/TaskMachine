<?php

namespace TaskFlux;

use Workflux\State\StateTrait;
use Workflux\Param\Output;
use Workflux\Param\InputInterface;
use Workflux\Param\OutputInterface;
use Workflux\State\StateInterface;

class Task implements StateInterface
{
    use StateTrait;

    private $initial;

    private $handler;

    public function execute(InputInterface $input): OutputInterface
    {
        return $this->handler->execute($input);
    }

    public function isInitial(): bool
    {
        return $this->settings->get('initial') === true;
    }

    public function isFinal(): bool
    {
        return $this->settings->get('final') === true;
    }

    public function setHandler($handler)
    {
        $this->handler = $handler;
    }
}
