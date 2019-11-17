<?php declare(strict_types=1);

namespace TaskMachine\Builder;

use Auryn\Injector;
use Ds\Map;
use Shrink0r\Monatic\Maybe;
use Shrink0r\PhpSchema\Factory as PhpSchemaFactory;
use Shrink0r\PhpSchema\Schema;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use TaskMachine\Handler\CallableTaskHandler;
use TaskMachine\Handler\TaskHandlerInterface;
use TaskMachine\Task\FinalTask;
use TaskMachine\Task\InitialTask;
use TaskMachine\Task\InteractiveTask;
use TaskMachine\Task\Task;
use Workflux\Builder\FactoryInterface;
use Workflux\Error\ConfigError;
use Workflux\Error\MissingImplementation;
use Workflux\Param\Settings;
use Workflux\State\StateInterface;
use Workflux\State\Validator;
use Workflux\State\ValidatorInterface;
use Workflux\Transition\ExpressionConstraint;
use Workflux\Transition\Transition;
use Workflux\Transition\TransitionInterface;

final class TaskFactory implements FactoryInterface
{
    const SUFFIX_IN = '-input_schema';

    const SUFFIX_OUT = '-output_schema';

    /** @var array */
    private static $defaultClasses = [
        'initial' => InitialTask::class,
        'interactive' => InteractiveTask::class,
        'task' => Task::class,
        'final' => FinalTask::class,
        'transition' => Transition::class
    ];

    /** @var array */
    private static $defaultValidationSchema = [':any_name:' => ['type' => 'any']];

    /** @var Injector */
    private $injector;

    /** @var Map */
    private $classMap;

    /** @var ExpressionLanguage */
    private $expressionEngine;

    public function __construct(
        Injector $injector = null,
        array $classMap = [],
        ExpressionLanguage $expressionEngine = null
    ) {
        $this->injector = $injector ?? new Injector;
        $this->expressionEngine = $expressionEngine ?? new ExpressionLanguage;
        $this->classMap = new Map(array_merge(self::$defaultClasses, $classMap));
    }

    public function createState(string $name, array $state = null): StateInterface
    {
        $state = Maybe::unit($state);
        $stateImplementor = $this->resolveStateImplementor($state);
        $settings = $state->input->get() ?? [];
        $settings['_map'] = $state->map->get() ?? [];
        $settings['_handler'] = $this->resolveHandler($state->handler->get());
        $stateInstance = new $stateImplementor(
            $name,
            new Settings($settings),
            $this->createValidator($name, $state),
            $this->expressionEngine
        );
        if ($state->final->get() && !$stateInstance->isFinal()) {
            throw new ConfigError("Trying to provide custom state that isn't final but marked as final in config.");
        }
        if ($state->initial->get() && !$stateInstance->isInitial()) {
            throw new ConfigError("Trying to provide custom state that isn't initial but marked as initial in config.");
        }
        if ($state->interactive->get() && !$stateInstance->isInteractive()) {
            throw new ConfigError(
                "Trying to provide custom state that isn't interactive but marked as interactive in config."
            );
        }
        return $stateInstance;
    }

    public function createTransition(string $from, string $to, array $config = null): TransitionInterface
    {
        $transition = Maybe::unit($config);
        if (is_string($transition->when->get())) {
            $config['when'] = [ $transition->when->get() ];
        }
        $implementor = $transition->class->get() ?? $this->classMap->get('transition');
        if (!in_array(TransitionInterface::class, class_implements($implementor))) {
            throw new MissingImplementation(
                'Trying to create transition without implementing required '.TransitionInterface::class
            );
        }
        $constraints = [];
        foreach (Maybe::unit($config)->when->get() ?? [] as $expression) {
            if (!is_string($expression)) {
                continue;
            }
            $constraints[] = new ExpressionConstraint($expression, $this->expressionEngine);
        }
        $settings = new Settings(Maybe::unit($config)->settings->get() ?? []);
        return new $implementor($from, $to, $settings, $constraints);
    }

    private function resolveStateImplementor(Maybe $state): string
    {
        switch (true) {
            case $state->initial->get():
                $stateImplementor = $this->classMap->get('initial');
                break;
            case $state->final->get() === true || $state->get() === null: // cast null to final-state by convention
                $stateImplementor = $this->classMap->get('final');
                break;
            case $state->interactive->get():
                $stateImplementor = $this->classMap->get('interactive');
                break;
            default:
                $stateImplementor = $this->classMap->get('task');
        }
        $stateImplementor = $state->class->get() ?? $stateImplementor;
        if (!in_array(StateInterface::class, class_implements($stateImplementor))) {
            throw new MissingImplementation(
                'Trying to use a custom task that does not implement required '.StateInterface::class
            );
        }
        return $stateImplementor;
    }

    private function createValidator(string $name, Maybe $state): ValidatorInterface
    {
        return new Validator(...$this->createValidationSchemas($name, (array)$state->validate->get()));
    }

    private function createValidationSchemas(string $name, array $validation = []): array
    {
        $inputSchema = self::$defaultValidationSchema;
        $outputSchema = self::$defaultValidationSchema;

        foreach ($validation as $expression => $type) {
            $path = explode('.', $expression);
            switch ($path[0]) {
                case 'input':
                    $inputSchema[$path[1]] = ['type' => $type];
                    break;
                case 'output':
                    $outputSchema[$path[1]] = ['type' => $type];
                    break;
                default:
                    throw new ConfigError("Invalid validation expression '$path[0]' in task '$name'");
            }
        }

        return [
            new Schema($name.self::SUFFIX_IN, ['type' => 'assoc', 'properties' => $inputSchema], new PhpSchemaFactory),
            new Schema($name.self::SUFFIX_OUT, ['type' => 'assoc', 'properties' => $outputSchema], new PhpSchemaFactory)
        ];
    }

    /** @param string|callable|TaskHandlerInterface $handler */
    private function resolveHandler($handler): TaskHandlerInterface
    {
        if ($handler instanceof TaskHandlerInterface) {
            return $handler;
        } elseif (is_callable($handler)) {
            return new CallableTaskHandler($this->injector, $handler);
        } elseif (class_exists($handler)) {
            return $this->injector->make($handler);
        }

        throw new ConfigError('Handler is not valid or not found.');
    }
}
