<?php

namespace EduLazaro\Laractions\Tests\Support;

use EduLazaro\Laractions\Action;

class FormatDateAction extends Action
{
    protected array $rules = [
        'date' => 'required|date',
    ];

    protected function handle(string $date): string
    {
        return date('Y-m-d', strtotime($date));
    }
}