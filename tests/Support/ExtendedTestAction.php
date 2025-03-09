<?php

namespace EduLazaro\Laractions\Tests\Support;

use EduLazaro\Laractions\Tests\Support\TestAction;

class ExtendedTestAction extends TestAction
{
    protected function handle(string $name, string $email): string
    {
        return $name . '_' . $email. '_ok';
    }
}
