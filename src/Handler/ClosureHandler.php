<?php

namespace TaskMachine\Handler;

use Auryn\Injector;
use Workflux\Param\InputInterface;

class ClosureHandler implements HandlerInterface
{
    private $handler;

    private $injector;

    public function __construct(\Closure $handler, Injector $injector)
    {
        $this->handler = $handler;
        $this->injector = $injector;
    }

    public function execute(InputInterface $input): array
    {
        $output = $this->injector->execute($this->handler, [ ':input' => $input ]);
        return (array)$output;
    }
}
