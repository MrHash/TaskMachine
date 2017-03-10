<?php

namespace TaskFlux\Handler;

use Workflux\Param\InputInterface;

interface HandlerInterface
{
    public function execute(InputInterface $input): array;
}
