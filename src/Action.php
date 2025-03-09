<?php

namespace EduLazaro\Laractions;

use ReflectionClass;
use ReflectionProperty;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;
use ReflectionMethod;
use LogicException;
use EduLazaro\Laractions\Jobs\ActionJob;

/**
 * Base action class
 */
abstract class Action
{
    /** @var object|null The actionable entity */
    protected ?object $actionable = null;

    /** @var array The rules array */
    protected array $rules = [];

    /** @var ActionJob The job matched with the action */
    protected ActionJob $actionJob;

    /** @var int Number of times the job should retry */
    protected int $tries = 1;

    /** @var int|null Delay in seconds before the job executes */
    protected ?int $delay = null;

    /** @var string|null The queue name */
    protected ?string $queue = null;

    /** @var bool Whether logging is enabled */
    protected bool $loggingEnabled = false;

    public function __invoke(...$params): mixed
    {
        return $this->run(...$params);
    }

    /**
     * Create a new instance via Laravel's service container.
     *
     * @param mixed ...$params Constructor arguments.
     * @return static
     */
    public static function create(...$params): static
    {
        return app(static::class, $params);
    }

    /**
     * Log a message from the action.
     *
     * @param string $message The log message.
     * @param array $context Additional context data.
     * @return void
     */
    protected function log(string $message, array $context = []): void
    {
        Log::info("[Action: " . static::class . "] " . $message, $context);
    }

    /**
     * Set the number of retries for the async action.
     *
     * @param int $times Number of retries.
     * @return static
     */
    public function retry(int $times): static
    {
        $this->tries = $times;
        return $this;
    }

    /**
     * Set a delay before the async action executes.
     *
     * @param int $seconds Delay in seconds.
     * @return static
     */
    public function delay(int $seconds): static
    {
        $this->delay = $seconds;
        return $this;
    }

    /**
     * Return action tries.
     *
     * @return int
     */
    public function getTries(): int
    {
        return $this->tries;;
    }

    /**
     * Set the queue where this action should be dispatched.
     *
     * @param string $queueName Queue name.
     * @return static
     */
    public function queue(string $queueName): static
    {
        $this->queue = $queueName;
        return $this;
    }

    /**
     * Enable or disable logging when the action is dispatched.
     *
     * @param bool $enabled Whether logging should be enabled.
     * @return static The modified action instance.
     */
    public function enableLogging(bool $enabled = true): static
    {
        $this->loggingEnabled = $enabled;
        return $this;
    }

    /**
     * Validate input parameters before execution.
     *
     * @param array $params Parameters to validate.
     * @return void
     * @throws ValidationException If validation fails.
     */
    protected function validate(array $params): void
    {
        if (empty($this->rules) || !is_array($this->rules)) {
            return;
        }

        $validator = Validator::make($params, $this->rules);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }

    /**
     * Set the actionable entity dynamically.
     *
     * @param object $actionable The entity to associate with the action.
     * @return $this
     */
    public function for(object $actionable): static
    {
        $this->actionable = $actionable;

        $actionableClasses = array_map(
            fn($class) => lcfirst(class_basename($class)),
            array_merge([get_class($actionable)], class_parents($actionable))
        );

        foreach ($actionableClasses as $actionableName) {
            if (property_exists($this, $actionableName)) {

                $this->{$actionableName} = $actionable;
                break;
            }
        }

        return $this;
    }

    /**
     * Injects parameters into class properties dynamically before execution.
     *
     * @param array $params The parameters to inject.
     * @return static
     */
    public function with(mixed ...$params): static
    {
        if (!empty($params[0]) && is_array($params[0])) {
            $params = $params[0];
        }

        $reflector = new ReflectionClass($this);
        $properties = $reflector->getProperties(ReflectionProperty::IS_PUBLIC | ReflectionProperty::IS_PROTECTED);

        foreach ($properties as $property) {
            $name = $property->getName();

            if (array_key_exists($name, $params) || isset($this->{$name})) {
                $property->setAccessible(true);
                $property->setValue($this, $params[$name] ?? $this->{$name});
            }
        }

        return $this;
    }

    /**
     * Get the actionable entity associated with the action.
     *
     * @return object|null The actionable entity, or null if none is set.
     */
    public function getActionable(): ?object
    {
        return $this->actionable;
    }

    /**
     * Execute the action.
     * Runs validation and passes the validated data to `action()`.
     *
     * @param array $params The attributes to validate and execute.
     * @return mixed
     */
    public function run(mixed ...$params): mixed
    {
        if (!empty($params[0]) && is_array($params[0])) {
            $params = $params[0];
        }

        if (array_key_first($params) == 0) {

            $reflection = new ReflectionMethod($this, 'action');
            $paramNames = [];

            foreach ($reflection->getParameters() as $index => $param) {
                $paramNames[$param->getName()] = $params[$index] ?? null;
            }

            $params = $paramNames;
        }

        $this->validate($params);

        if (method_exists($this, 'handle')) {
            return $this->handle(...$params);
        }

        throw new LogicException("The action class " . static::class . " must implement a `action` method.");
    }

    /**
     * Dispatch the action asynchronously with configured options.
     *    
     * @param array $params The parameters to set.
     * @return void
     */
    public function dispatch(mixed ...$params): void
    {
        if (!empty($params[0]) && is_array($params[0])) {
            $params = $params[0];
        }

        $this->actionJob = new ActionJob($this, $params);

        if ($this->loggingEnabled) {
            Log::info("[Action: " . static::class . "] Dispatching job.", [
                'queue' => $this->queue,
                'delay' => $this->delay ?? 0,
                'tries' => $this->tries,
                'params' => $params
            ]);
        }

        dispatch($this->actionJob)
            ->onQueue($this->queue ?? 'default')
            ->delay($this->delay ?? 0);
    }
}
