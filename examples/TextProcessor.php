<?php

use TaskMachine\Handler\TaskHandlerInterface;
use Workflux\Param\InputInterface;

class TextProcessor implements TaskHandlerInterface
{
    private $settings;

    public function __construct(array $settings)
    {
        $this->settings = $settings;
    }

    public function handle(InputInterface $input): array
    {
        $text = $input->get('text');

        switch ($this->settings['process']) {
            case 'reverse':
                $processed = strrev($text);
                break;
            case 'upper':
                $processed = strtoupper($text);
                break;
            case 'lower':
                $processed = strtolower($text);
                break;
            default:
                throw new \InvalidArgumentException('Unknown process.');
        }

        return ['text' => $processed];
    }
}
