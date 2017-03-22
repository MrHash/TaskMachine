<?php

namespace TaskMachine\Builder;

use Shrink0r\PhpSchema\Builder;
use Shrink0r\PhpSchema\SchemaInterface;

class TaskBuilder extends Builder
{
    private $context;

    public function __construct(TaskMachineBuilder $context, SchemaInterface $schema = null)
    {
        $this->context = $context;
        parent::__construct($schema);
    }

    //@todo override builder methods

    public function _build()
    {
        return parent::build();
    }

    public function build(array $defaults = [])
    {
        return $this->context->build();
    }
}
