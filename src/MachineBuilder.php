<?php

namespace TaskFlux;

use Shrink0r\PhpSchema\Builder;

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

    public function getStackImplementor()
    {
        return MachineBuilderStack::CLASS;
    }

    public function build(array $defaults = [])
    {
//         var_dump($this->data); die;
        return parent::build($defaults);
    }
}