<?php

namespace EduLazaro\Laractions\Tests\Support;

use EduLazaro\Laractions\Action;

class SingleScalarAction extends Action
{
    protected function handle(string $concept): string
    {
        return $concept;
    }
}
