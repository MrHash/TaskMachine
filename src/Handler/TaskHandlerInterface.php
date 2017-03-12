<?php

namespace TaskMachine\Handler;

use Workflux\Param\InputInterface;
use Workflux\Param\Settings;

interface TaskHandlerInterface
{
    public function execute(InputInterface $input, Settings $settings): array;
}
