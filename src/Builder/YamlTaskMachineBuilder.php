<?php

namespace TaskMachine\Builder;

use Symfony\Component\Yaml\Parser;
use TaskMachine\TaskMachine;
use TaskMachine\TaskMachineInterface;
use Workflux\Builder\FactoryInterface;
use Workflux\Error\ConfigError;

class YamlTaskMachineBuilder extends TaskMachineBuilder
{
    private $parser;

    private $yamlFilePaths;

    public function __construct(array $yamlFilePaths, FactoryInterface $factory = null)
    {
        foreach ($yamlFilePaths as $yamlFilePath) {
            if (!is_readable($yamlFilePath)) {
                throw new ConfigError("Trying to load non-existent taskmachine configurtion at: $yamlFilePath");
            }
        }

        $this->parser = new Parser;
        $this->yamlFilePaths = $yamlFilePaths;
        parent::__construct($factory);
    }

    public function build(array $defaults = []): TaskMachineInterface
    {
        foreach ($this->yamlFilePaths as $yamlFilePath) {
            $data = array_replace_recursive($this->parser->parse(file_get_contents($yamlFilePath)), $data ?? []);
        }

        $machines = $data['machines'] ?? [];
        foreach ($machines as $name => &$machine) {
            foreach ($machine as $task => &$definition) {
                if (!isset($data['tasks'][$task])) {
                    throw new ConfigError("Task definition for '$task' not found");
                }
                $definition = array_replace_recursive($definition, $data['tasks'][$task]);
            }
        }

        // merge in fluently specified machines
        $machines = array_replace_recursive($machines, $this->buildMachines());

        return new TaskMachine($machines, $this->factory);
    }
}
