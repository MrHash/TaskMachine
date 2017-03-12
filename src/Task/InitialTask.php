<?php

namespace TaskMachine\Task;

use Workflux\Param\InputInterface;
use Workflux\State\StateInterface;
use Workflux\State\StateTrait;

final class InitialTask implements StateInterface
{
    use StateTrait;

    private function generateOutputParams(InputInterface $input): array
    {
        return $this->settings->get('handler')->execute($input, $this->settings);
    }

    public function isInitial(): bool
    {
        return true;
    }
}
