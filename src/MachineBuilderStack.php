<?php

namespace TaskFlux;

use Shrink0r\PhpSchema\BuilderStack;

class MachineBuilderStack extends BuilderStack
{
    public function finally($task)
    {
        return $this->rewind()->finally($task);
    }

    public function task($task)
    {
        return $this->rewind()->task($task);
    }

    public function when($condition, $target)
    {
        $this->transitions->{$target}->__call('when', [$condition]);
        return $this;
    }

    public function then($target)
    {
        return $this->transitions->{$target};
    }

    public function expects(array $input)
    {
        $this->input_schema->{current($input)}->type(key($input));
        return $this;
    }
}
