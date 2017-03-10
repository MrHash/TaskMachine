<?php

namespace TaskFlux;

use TaskFlux\Handler\HandlerInterface;
use Workflux\Param\InputInterface;
use Workflux\Param\Output;
use Workflux\Param\OutputInterface;

class CustomHandler implements HandlerInterface
{
    private $environment;

    public function __construct(Environment $environment)
    {
        $this->environment = $environment;
    }

    public function execute(InputInterface $input): array
    {
        echo $this->environment->get('custom').PHP_EOL;
        return ['some' => 'thing'];
    }
}
