<?php declare(strict_types=1);

namespace TaskMachine\Builder;

use Shrink0r\PhpSchema\Builder;
use Shrink0r\PhpSchema\SchemaInterface;

class MachineBuilder extends Builder
{
    /** @var TaskMachineBuilderInterface */
    private $context;

    public function __construct(TaskMachineBuilderInterface $context, SchemaInterface $schema = null)
    {
        $this->context = $context;
        parent::__construct($schema);
    }

    //@todo override builder methods

    // phpcs:ignore PSR2.Methods.MethodDeclaration.Underscore
    public function _build()
    {
        return parent::build();
    }

    public function build(array $defaults = [])
    {
        return $this->context->build();
    }
}
