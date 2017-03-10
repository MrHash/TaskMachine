<?php

namespace TaskMachine\Builder;

use Shrink0r\PhpSchema\Builder;

class TaskBuilder extends Builder
{
    public function input(array $input)
    {
        return $this->input_schema->{current($input)}->type(key($input))->rewind();
    }

    public function output(array $output)
    {
        return $this->output_schema->{current($output)}->type(key($output))->rewind();
    }
}
