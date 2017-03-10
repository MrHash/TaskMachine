<?php

namespace TaskFlux;

use Shrink0r\PhpSchema\BuilderStack;

class MachineBuilderStack extends BuilderStack
{
    public function then($target)
    {
        return $this->transitions([ $target => null ])->rewind();
    }

    public function initial()
    {
        parent::__call('initial', [true]);
        return $this;
    }

    public function final()
    {
        parent::__call('final', [true])->transitions([]);
        return $this;
    }
}
