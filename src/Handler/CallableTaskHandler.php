<?php

namespace TaskMachine\Handler;

use Auryn\Injector;
use Workflux\Param\InputInterface;
use Workflux\Param\Input;
use Workflux\Param\Settings;

class CallableTaskHandler implements TaskHandlerInterface
{
    private $handler;

    private $injector;

    public function __construct($handler, Injector $injector)
    {
        $this->handler = $handler;
        $this->injector = $injector;
    }

    public function execute(InputInterface $input, Settings $settings): array
    {
        $this->injector->share($input)->alias(InputInterface::class, Input::class);
        $this->injector->share($settings);
        $output = $this->injector->execute($this->handler);
        return (array) $output;
    }
}
