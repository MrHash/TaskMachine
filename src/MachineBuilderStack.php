<?php

namespace TaskFlux;

use Shrink0r\PhpSchema\BuilderStack;

class MachineBuilderStack extends BuilderStack
{
    public function when($condition, $target)
    {
        $this->transitions->{$target}->__call('when', [$condition]);
        return $this;
    }

    public function continue()
    {
        return $this->rewind();
    }

    public function then($target)
    {
        return $this->transitions->{$target}->rewind();
    }

    public function expects(array $input)
    {
        $this->input_schema->{current($input)}->type(key($input));
        return $this;
    }
}
