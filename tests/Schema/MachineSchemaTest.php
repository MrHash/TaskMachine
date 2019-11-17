<?php

namespace TaskMachine\Tests\Builder;

use PHPUnit\Framework\TestCase;
use Shrink0r\PhpSchema\FactoryInterface;
use TaskMachine\Schema\MachineSchema;

final class MachineSchemaTest extends TestCase
{
    public function testGetName()
    {
        $schema = new MachineSchema;
        $this->assertEquals('taskmachine', $schema->getName());
    }

    public function testGetType()
    {
        $schema = new MachineSchema;
        $this->assertEquals('assoc', $schema->getType());
    }

    public function testGetCustomTypes()
    {
        $schema = new MachineSchema;
        $this->assertEquals([], array_keys($schema->getCustomTypes()));
    }

    public function testGetProperties()
    {
        $schema = new MachineSchema;
        $expected_keys = [ ':any_name:' ];
        foreach (array_keys($schema->getProperties()) as $key) {
            $this->assertContains($key, $expected_keys);
        }
    }

    public function testGetFactory()
    {
        $schema = new MachineSchema;
        $this->assertInstanceOf(FactoryInterface::class, $schema->getFactory());
    }
}
