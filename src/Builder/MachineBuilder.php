<?php

namespace TaskMachine\Builder;

use Shrink0r\PhpSchema\Builder;
use Shrink0r\PhpSchema\SchemaInterface;
use TaskMachine\TaskMachine;

class MachineBuilder extends Builder
{
    private $context;

    public function __construct(TaskMachine $context, SchemaInterface $schema = null)
    {
        $this->context = $context;
        parent::__construct($schema);
    }

    public function first($name)
    {
        $state = $this->states->{$name};
        $state->initial(true);
        return $state;
    }

    public function task($name)
    {
        return $this->states->{$name};
    }

    public function finally($name)
    {
        return $this->states->{$name}->final(true)->transitions([])->rewind();
    }

    public function buildConfig(array $defaults = [])
    {
        return parent::build($defaults);
    }

    public function build(array $defaults = [])
    {
        return $this->context->build($this->valueOf('name'), $defaults);
    }

    public function getStackImplementor(): String
    {
        return MachineBuilderStack::CLASS;
    }
}
