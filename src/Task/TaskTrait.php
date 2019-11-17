<?php declare(strict_types=1);

namespace TaskMachine\Task;

use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Workflux\Error\ConfigError;
use Workflux\Param\InputInterface;
use Workflux\Param\Output;
use Workflux\Param\OutputInterface;
use Workflux\Param\ParamHolderInterface;
use Workflux\State\ValidatorInterface;

trait TaskTrait
{
    /** @var string */
    private $name;

    /** @var ParamHolderInterface */
    private $settings;

    /** @var ValidatorInterface */
    private $validator;

    /** @var ExpressionLanguage */
    private $expressionEngine;

    public function __construct(
        string $name,
        ParamHolderInterface $settings,
        ValidatorInterface $validator,
        ExpressionLanguage $expressionEngine
    ) {
        $this->name = $name;
        $this->settings = $settings;
        $this->validator = $validator;
        $this->expressionEngine = $expressionEngine;
        foreach ($this->getRequiredSettings() as $settingName) {
            if (!$this->settings->has($settingName)) {
                throw new ConfigError("Trying to configure state '$name' without required setting '$settingName'.");
            }
        }
    }

    public function execute(InputInterface $input): OutputInterface
    {
        $input = $input->withParams($this->settings->toArray());
        $this->validator->validateInput($this, $input);
        $output = $this->generateOutput($input);
        $this->validator->validateOutput($this, $output);
        return $output;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function isInitial(): bool
    {
        return false;
    }

    public function isFinal(): bool
    {
        return false;
    }

    public function isInteractive(): bool
    {
        return false;
    }

    public function getValidator(): ValidatorInterface
    {
        return $this->validator;
    }

    /** @return mixed */
    public function getSetting(string $name, $default = null)
    {
        return $this->settings->get($name) ?? $default;
    }

    public function getSettings(): ParamHolderInterface
    {
        return $this->settings;
    }

    private function generateOutput(InputInterface $input): OutputInterface
    {
        $output = $this->generateOutputParams($input);

        return new Output(
            $this->name,
            array_merge(
                $output,
                $this->evaluateOutputMapping($input, new Output($this->name, $output))
            )
        );
    }

    private function generateOutputParams(InputInterface $input): array
    {
        $handler = $this->getSetting('_handler');
        $input = $input->withParams($this->settings->withoutParams(['_handler', '_map'])->toArray());
        return $handler->handle($input);
    }

    private function evaluateOutputMapping(InputInterface $input, OutputInterface $output): array
    {
        foreach ($this->getSetting('_map', []) as $expression => $key) {
            $mappedOutput[$key] = $this->expressionEngine->evaluate(
                $expression,
                ['input' => $input, 'output' => $output]
            );
        }
        return $mappedOutput ?? [];
    }

    private function getRequiredSettings(): array
    {
        return [];
    }
}
