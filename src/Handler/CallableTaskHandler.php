<?php declare(strict_types=1);

namespace TaskMachine\Handler;

use Auryn\Injector;
use Workflux\Param\InputInterface;
use Workflux\Param\Input;

class CallableTaskHandler implements TaskHandlerInterface
{
    /** @var Injector */
    private $injector;

    /** @var callable */
    private $handler;

    public function __construct(Injector $injector, callable $handler)
    {
        $this->injector = $injector;
        $this->handler = $handler;
    }

    public function execute(InputInterface $input): array
    {
        $this->injector->share($input)->alias(InputInterface::class, Input::class);
        $output = $this->injector->execute($this->handler);
        return $output instanceof InputInterface
            ? $output->getParams()
            : (array)$output;
    }
}
