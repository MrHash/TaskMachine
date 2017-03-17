<?php

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
    /**
     * @var string $name
     */
    private $name;

    /**
     * @var ParamHolderInterface $settings
     */
    private $settings;

    /**
     * @var ValidatorInterface $schemas
     */
    private $validator;

    /**
     * @var ExpressionLanguage $expression_engine
     */
    private $expression_engine;

    /**
     * @param string $name
     * @param ParamHolderInterface $settings
     * @param ValidatorInterface $validator
     * @param ExpressionLanguage $expression_engine
     */
    public function __construct(
        string $name,
        ParamHolderInterface $settings,
        ValidatorInterface $validator,
        ExpressionLanguage $expression_engine
    ) {
        $this->name = $name;
        $this->settings = $settings;
        $this->validator = $validator;
        $this->expression_engine = $expression_engine;
        foreach ($this->getRequiredSettings() as $setting_name) {
            if (!$this->settings->has($setting_name)) {
                throw new ConfigError("Trying to configure state '$name' without required setting '$setting_name'.");
            }
        }
    }

    /**
     * @param InputInterface $input
     *
     * @return OutputInterface
     */
    public function execute(InputInterface $input): OutputInterface
    {
        $input = $input->withParams($this->settings->toArray());
        $this->validator->validateInput($this, $input);
        $output = $this->generateOutput($input);
        $this->validator->validateOutput($this, $output);
        return $output;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return bool
     */
    public function isInitial(): bool
    {
        return false;
    }

    /**
     * @return bool
     */
    public function isFinal(): bool
    {
        return false;
    }

    /**
     * @return bool
     */
    public function isInteractive(): bool
    {
        return false;
    }

     /**
     * @return ValidatorInterface
     */
    public function getValidator(): ValidatorInterface
    {
        return $this->validator;
    }

    /**
     * @param string $name
     * @param mixed $default
     *
     * @return mixed
     */
    public function getSetting(string $name, $default = null)
    {
        return $this->settings->get($name) ?? $default;
    }

    /**
     * @return ParamHolderInterface
     */
    public function getSettings(): ParamHolderInterface
    {
        return $this->settings;
    }

    /**
     * @param InputInterface $input
     *
     * @return OutputInterface
     */
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

    /**
     * @param  InputInterface $input
     *
     * @return mixed[]
     */
    private function generateOutputParams(InputInterface $input): array
    {
        $handler = $this->getSetting('_handler');
        $input = $input->withParams($this->settings->withoutParams(['_handler', '_map'])->toArray());
        return $handler->execute($input);
    }

    private function evaluateOutputMapping(InputInterface $input, OutputInterface $output)
    {
        foreach ($this->getSetting('_map', []) as $expression => $key) {
            $mappedOutput[$key] = $this->expression_engine->evaluate(
                $expression,
                ['input' => $input, 'output' => $output]
            );
        }
        return $mappedOutput ?? [];
    }

    /**
     * @return string[]
     */
    private function getRequiredSettings(): array
    {
        return [];
    }
}
