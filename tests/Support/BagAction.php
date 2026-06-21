<?php

namespace EduLazaro\Laractions\Tests\Support;

use EduLazaro\Laractions\Action;

class BagAction extends Action
{
    protected function handle(array $attributes): array
    {
        return $attributes;
    }
}
