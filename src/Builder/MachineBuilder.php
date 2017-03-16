<?php

namespace TaskMachine\Builder;

use Shrink0r\PhpSchema\Builder;
use Shrink0r\PhpSchema\SchemaInterface;

class MachineBuilder extends Builder
{
    private $context;

    public function __construct(TaskMachineBuilder $context, SchemaInterface $schema = null)
    {
        $this->context = $context;
        parent::__construct($schema);
    }

    //@todo override builder methods

    public function buildConfig(array $defaults = [])
    {
        return parent::build($defaults);
    }

    public function build(array $defaults = [])
    {
        return $this->context->build($defaults);
    }
}
