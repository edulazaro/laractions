<?php

namespace EduLazaro\Laractions\Tests\Support;

use EduLazaro\Laractions\Tests\Support\TestAction;
use EduLazaro\Laractions\Tests\Support\TestActionDefaultValue;
use EduLazaro\Laractions\Tests\Support\ExtendedTestAction;
use EduLazaro\Laractions\Concerns\HasActions;

class TestEntity
{
    protected $actions = [
        'test_action' => TestAction::class,
        'test_action_default_value' => TestActionDefaultValue::class,
        'extended_test_action' => ExtendedTestAction::class,
    ];

    use HasActions;
    
    public string $name = "Test Entity";
}
