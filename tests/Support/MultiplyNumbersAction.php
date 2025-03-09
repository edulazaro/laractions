<?php

namespace EduLazaro\Laractions\Tests\Support;

use EduLazaro\Laractions\Action;

class MultiplyNumbersAction extends Action
{
    protected array $rules = [
        'a' => 'required|numeric',
        'b' => 'required|numeric',
    ];

    protected function handle(float $a, float $b): float
    {
        return $a * $b;
    }
}
