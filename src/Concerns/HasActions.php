<?php

namespace EduLazaro\Laractions\Concerns;

use Illuminate\Support\Facades\App;
use Exception;

/**
 * Trait to allow an entity to execute actions
 */
trait HasActions
{
    /**
     * @var array $actions Stores the actions mapping
     */
    protected array $actions = [];

    /**
     * @var array $mockedActions Stores mocked action instances for testing purposes.
     */
    protected array $mockedActions = [];

    /**
     * Mock an action instance for a specific action class.
     *
     * @param string $actionClass The fully qualified class name of the action.
     * @param mixed $mockAction The mocked action instance.
     * @return $this
     */
    public function mockAction(string $actionClass, $mockAction): static
    {
        $this->mockedActions[$actionClass] = $mockAction;

        return $this;
    }

    /**
     * Resolve and execute an action associated with this entity.
     *
     * @param string $actionClass The key or fully qualified class name of the action.
     * @param array $params Optional parameters to pass to the action.
     * @return mixed The resolved action instance.
     * 
     * @throws Exception If the action is not defined in the model's `$actions` array.
     */
    public function action(string $actionClass, array $params = []): mixed
    {
        if (!class_exists($actionClass)) {
            if (!isset($this->actions[$actionClass])) {
                throw new Exception("Action `{$actionClass}` is not defined in the model's actions array.");
            }

            $actionClass = $this->actions[$actionClass];
        }

        if (App::runningUnitTests() && isset($this->mockedActions[$actionClass])) {
            return $this->mockedActions[$actionClass]->on($this);
        }

        return App::makeWith($actionClass, $params)->on($this);
    }
}