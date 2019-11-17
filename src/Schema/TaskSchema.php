<?php declare(strict_types=1);

namespace TaskMachine\Schema;

use Shrink0r\PhpSchema\Factory;
use Shrink0r\PhpSchema\FactoryInterface;
use Shrink0r\PhpSchema\Property\PropertyInterface;
use Shrink0r\PhpSchema\ResultInterface;
use Shrink0r\PhpSchema\Schema;
use Shrink0r\PhpSchema\SchemaInterface;

final class TaskSchema implements SchemaInterface
{
    /** @var SchemaInterface */
    private $internalSchema;

    public function __construct()
    {
        $this->internalSchema = new Schema('task', [
            'type' => 'assoc',
            'properties' => [
                "handler" => [ "type" => "any" ],
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
     * @return ResultInterface Returns Ok on success; otherwise Error.
     */
    public function validate(array $data): ResultInterface
    {
        return $this->internalSchema->validate($data);
    }

    public function getName(): string
    {
        return $this->internalSchema->getName();
    }

    /**
     * Returns the schema type. Atm only 'assoc' is supported.
     */
    public function getType(): string
    {
        return $this->internalSchema->getType();
    }

    /**
     * Returns the custom-types that have been defined for the schema.
     *
     * @return SchemaInterface[]
     */
    public function getCustomTypes(): array
    {
        return $this->internalSchema->getCustomTypes();
    }

    /**
     * Returns the schema's properties.
     *
     * @return PropertyInterface[]
     */
    public function getProperties(): array
    {
        return $this->internalSchema->getProperties();
    }

    /**
     * Returns the factory, that is used by the schema.
     */
    public function getFactory(): FactoryInterface
    {
        return $this->internalSchema->getFactory();
    }
}
