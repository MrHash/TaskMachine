<?php

namespace TaskMachine\Builder;

use Symfony\Component\Yaml\Parser;
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
                throw new ConfigError("Trying to load non-existent taskmachine configuration at: $yamlFilePath");
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

        foreach ($data['tasks'] ?? [] as $name => $config) {
            $this->addTask($name, $config);
        }

        foreach ($data['machines'] ?? [] as $name => $config) {
            $this->addMachine($name, $config);
        }

        return parent::build($defaults);
    }
}
