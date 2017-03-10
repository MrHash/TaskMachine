<?php

namespace TaskFlux;

use Shrink0r\PhpSchema\BuilderStack;

class MachineBuilderStack extends BuilderStack
{
    public function then($target)
    {
        return $this->transitions([ $target => null ])->rewind();
    }
}
