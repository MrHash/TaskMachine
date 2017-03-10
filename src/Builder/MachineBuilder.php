<?php

namespace TaskFlux\Builder;

use Shrink0r\PhpSchema\Builder;
use Shrink0r\PhpSchema\SchemaInterface;

class MachineBuilder extends Builder
{
    public function first($name)
    {
        $state = $this->states->{$name};
        $state->initial(true);
        return $state;
    }

    public function finally($name)
    {
        return $this->states->{$name}->final(true)->transitions([])->rewind();
    }

    public function task($name)
    {
        return $this->states->{$name};
    }

    public function getStackImplementor(): String
    {
        return MachineBuilderStack::CLASS;
    }
}
