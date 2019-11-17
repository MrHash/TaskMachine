<?php declare(strict_types=1);

namespace TaskMachine\Builder;

use Symfony\Component\Yaml\Parser;
use TaskMachine\TaskMachineInterface;
use Workflux\Builder\FactoryInterface;
use Workflux\Error\ConfigError;

class YamlTaskMachineBuilder extends TaskMachineBuilder
{
    /** @var Parser */
    private $parser;

    /** @var array */
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

    public function build(): TaskMachineInterface
    {
        foreach ($this->yamlFilePaths as $yamlFilePath) {
            $data = array_replace_recursive($this->parser->parse(file_get_contents($yamlFilePath)), $data ?? []);
        }

        $this->tasks = array_merge($this->tasks, $data['tasks'] ?? []);
        $this->machines = array_merge($this->machines, $data['machines'] ?? []);

        return parent::build();
    }
}
