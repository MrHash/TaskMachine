<?php

namespace TaskMachine\Builder;

use Shrink0r\PhpSchema\BuilderStack;

class MachineBuilderStack extends BuilderStack
{
    public function finally(string $task): MachineBuilder
    {
        return $this->rewind()->finally($task);
    }

    public function task(string $task): MachineBuilderStack
    {
        return $this->rewind()->task($task);
    }

    public function map(array $mappings)
    {
        foreach ($mappings as $from => $to) {
            $this->settings->_map->{$from}($to);
        }
        return $this;
    }

    public function with(array $inputs)
    {
        foreach ($inputs as $key => $value) {
            $this->settings->{$key}($value);
        }
        return $this;
    }

    public function when(array $transitions): MachineBuilderStack
    {
        foreach ($transitions as $condition => $target) {
            $this->transitions->{$target}->__call('when', [$condition]);
        }
        return $this;
    }

    public function then(string $target): MachineBuilderStack
    {
        return $this->transitions->{$target};
    }
}
