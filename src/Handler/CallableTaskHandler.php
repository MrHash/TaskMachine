<?php

namespace TaskMachine\Handler;

use Auryn\Injector;
use Workflux\Param\InputInterface;
use Workflux\Param\Input;

class CallableTaskHandler implements TaskHandlerInterface
{
    private $handler;

    private $injector;

    public function __construct($handler, Injector $injector)
    {
        $this->handler = $handler;
        $this->injector = $injector;
    }

    public function execute(InputInterface $input): array
    {
        $this->injector->share($input)->alias(InputInterface::class, Input::class);
        $output = $this->injector->execute($this->handler);
        return (array) $output;
    }
}
