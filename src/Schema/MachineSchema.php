<?php declare(strict_types=1);

namespace TaskMachine\Schema;

use Shrink0r\PhpSchema\Factory;
use Shrink0r\PhpSchema\FactoryInterface;
use Shrink0r\PhpSchema\Property\PropertyInterface;
use Shrink0r\PhpSchema\ResultInterface;
use Shrink0r\PhpSchema\Schema;
use Shrink0r\PhpSchema\SchemaInterface;

final class MachineSchema implements SchemaInterface
{
    /** @var SchemaInterface */
    private $internalSchema;

    public function __construct()
    {
        $this->internalSchema = new Schema('taskmachine', [
            'type' => 'assoc',
            'properties' => [
                ':any_name:' => $this->getTaskSchema()
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

    /**
     * Return php-schema definition that reflects the structural expectations towards task data.
     */
    private function getTaskSchema(): array
    {
        return [
            'type' => 'assoc' ,
            'required' => false,
            'properties' => [
                'task' => [ 'type' => 'string', 'required' => false ],
                'handler' => [ 'type' => 'any', 'required' => false ], // @todo callable, object or null
                'initial' => [ 'type' => 'bool', 'required' => false ],
                'final' => [ 'type' => 'bool', 'required' => false ],
                'interactive' => [ 'type' => 'bool', 'required' => false ],
                'input' => [
                    'type' => 'assoc',
                    'required' => false,
                    'properties' => [
                        ':any_name:' => [ 'type' => 'any' ]
                    ]
                ],
                'map' => [
                    'type' => 'assoc',
                    'required' => false,
                    'properties' => [
                        ':any_name:' => [ 'type' => 'any' ]
                    ]
                ],
                'transition' => [
                    'type' => 'any', //@todo array or string
                    'required' => false //@todo required
                ],
                'validate' => [
                    'type' => 'assoc',
                    'required' => false,
                    'properties' => [
                        ':any_name:' => [ 'type' => 'any' ]
                    ]
                ]
            ]
        ];
    }
}
