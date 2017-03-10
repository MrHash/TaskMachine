<?php

namespace TaskFlux;

use Shrink0r\PhpSchema\Builder;

class MachineBuilder extends Builder
{
    public function run($name)
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