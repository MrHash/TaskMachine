<?php

namespace TaskMachine\Schema;

use Shrink0r\PhpSchema\Factory;
use Shrink0r\PhpSchema\FactoryInterface;
use Shrink0r\PhpSchema\ResultInterface;
use Shrink0r\PhpSchema\Schema;
use Shrink0r\PhpSchema\SchemaInterface;

final class MachineSchema implements SchemaInterface
{
    /**
     * @var SchemaInterface $internal_schema
     */
    private $internal_schema;

    public function __construct()
    {
        $this->internal_schema = new Schema('taskmachine', [
            'type' => 'assoc',
            'properties' => [
                ":any_name:" => $this->getTaskSchema()
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

    /**
     * Return php-schema definition that reflects the structural expectations towards task data.
     *
     * @return mixed[]
     */
    private function getTaskSchema(): array
    {
        return [
            "type" => "assoc" ,
            "required" => false,
            "properties" => [
                "handler" => [ "type" => "any", 'required' => false ], // @todo callable, object or null
                "initial" => [ "type" => "bool", "required" => false ],
                "final" => [ "type" => "bool", "required" => false ],
                "interactive" => [ "type" => "bool", "required" => false ],
                "input" => [
                    "type" => "assoc",
                    "required" => false,
                    "properties" => [
                        ":any_name:" => [ "type" => "any" ]
                    ]
                ],
                "map" => [
                    "type" => "assoc",
                    "required" => false,
                    "properties" => [
                        ":any_name:" => [ "type" => "any" ]
                    ]
                ],
                "transition" =>  [
                    "type" => "any", //@todo array or string
                    "required" => false //@todo required
                ],
                "validate" =>  [
                    "type" => "assoc",
                    "required" => false,
                    "properties" => [
                        ":any_name:" => [ "type" => "any" ]
                    ]
                ]
            ]
        ];
    }
}
