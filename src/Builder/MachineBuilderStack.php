<?php

namespace TaskMachine\Builder;

use Shrink0r\PhpSchema\BuilderStack;

class MachineBuilderStack extends BuilderStack
{
    public function finally($task): MachineBuilder
    {
        return $this->rewind()->finally($task);
    }

    public function task($task): MachineBuilderStack
    {
        return $this->rewind()->task($task);
    }

    public function with(array $setting)
    {
        $this->settings->{key($setting)}(current($setting));
        return $this;
    }

    public function when($condition, $target): MachineBuilderStack
    {
        $this->transitions->{$target}->__call('when', [$condition]);
        return $this;
    }

    public function then($target): MachineBuilderStack
    {
        return $this->transitions->{$target};
    }
}
