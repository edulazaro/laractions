<?php

namespace EduLazaro\Laractions\Concerns;

use Illuminate\Support\Facades\App;
use EduLazaro\Laractions\Action;

trait IsActor
{
    /**
     * Acts as this actor and executes the given action.
     *
     * @param string $actionClass
     * @return Action
     */
    public function act(string $actionClass, array $params = []): mixed
    {
        return App::makeWith($actionClass, $params)->actor($this);
    }
}
