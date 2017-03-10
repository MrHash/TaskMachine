<?php

namespace TaskFlux;

use Shrink0r\PhpSchema\Builder;

class MachineBuilder extends Builder
{
    public function task($name)
    {
        return $this->states->{$name};
    }

    public function getStackImplementor()
    {
        return MachineBuilderStack::CLASS;
    }
}