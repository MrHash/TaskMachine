<?php

namespace TaskFlux;

use Workflux\Param\InputInterface;
use Workflux\Param\OutputInterface;

interface HandlerInterface
{
    public function execute(InputInterface $input): OutputInterface;
}