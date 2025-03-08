<?php

namespace EduLazaro\Laractions;

use ReflectionClass;
use ReflectionProperty;
use InvalidArgumentException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;
use LogicException;

/**
 * Base action class
 */
abstract class Action
{
    /** @var object|null The actionable entity */
    protected ?object $actionable = null;

    protected array $rules = [];

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
     * Validate input parameters before execution.
     *
     * @param array $params Parameters to validate.
     * @return void
     * 
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
     *
     * @throws InvalidArgumentException If the actionable entity is not an object.
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
     * @return $this
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
     * Execute the action.
     * Runs validation and passes the validated data to `handle()`.
     *
     * @param array $params The attributes to validate and execute.
     * @return mixed
     */
    public function run(mixed ...$params): mixed
    {
        if (!empty($params[0]) && is_array($params[0])) {
            $params = $params[0];
        }

        $this->validate($params);

        if (method_exists($this, 'handle')) {
            return $this->handle(...$params);
        }

        throw new LogicException("The action class " . static::class . " must implement a `handle` method.");
    }
}
