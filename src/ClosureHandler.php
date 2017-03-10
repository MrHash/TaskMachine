<?php

namespace TaskFlux;

use Auryn\Injector;
use Workflux\Param\InputInterface;
use Workflux\Param\OutputInterface;
use Workflux\Param\Output;

class ClosureHandler implements HandlerInterface
{
    private $name;

    private $handler;

    private $injector;

    public function __construct($name, \Closure $handler, Injector $injector)
    {
        $this->name = $name;
        $this->handler = $handler;
        $this->injector = $injector;
    }

    public function execute(InputInterface $input): OutputInterface
    {
        $output = $this->injector->execute($this->handler, [ ':input' => $input ]);
        return new Output($this->name, (array)$output);
    }
}