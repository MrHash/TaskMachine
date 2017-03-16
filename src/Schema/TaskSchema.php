<?php

namespace TaskMachine\Schema;

use Shrink0r\PhpSchema\Factory;
use Shrink0r\PhpSchema\FactoryInterface;
use Shrink0r\PhpSchema\ResultInterface;
use Shrink0r\PhpSchema\Schema;
use Shrink0r\PhpSchema\SchemaInterface;

final class TaskSchema implements SchemaInterface
{
    /**
     * @var SchemaInterface $internal_schema
     */
    private $internal_schema;

    public function __construct()
    {
        $this->internal_schema = new Schema('task', [
            'type' => 'assoc',
            'properties' => [
                "validate" =>  [
                    "type" => "assoc",
                    "required" => false,
                    "properties" => [
                        ":any_name:" => [ "type" => "any" ]
                    ]
                ]
            ]
        ], new Factory);
    }

    /**
     * Verify that the given data is structured according to the scheme.
     *
     * @param mixed[] $data
     *
     * @return ResultInterface Returns Ok on success; otherwise Error.
     */
    public function validate(array $data): ResultInterface
    {
        return $this->internal_schema->validate($data);
    }

    /**
     * Returns the schema's name.
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->internal_schema->getName();
    }

    /**
     * Returns the schema type. Atm only 'assoc' is supported.
     *
     * @return string
     */
    public function getType(): string
    {
        return $this->internal_schema->getType();
    }

    /**
     * Returns the custom-types that have been defined for the schema.
     *
     * @return SchemaInterface[]
     */
    public function getCustomTypes(): array
    {
        return $this->internal_schema->getCustomTypes();
    }

    /**
     * Returns the schema's properties.
     *
     * @return Property\PropertyInterface[]
     */
    public function getProperties(): array
    {
        return $this->internal_schema->getProperties();
    }

    /**
     * Returns the factory, that is used by the schema.
     *
     * @return FactoryInterface
     */
    public function getFactory(): FactoryInterface
    {
        return $this->internal_schema->getFactory();
    }
}
