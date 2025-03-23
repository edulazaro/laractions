<?php

namespace EduLazaro\Laractions\Tests\Support;

use Illuminate\Database\Eloquent\Model;
use EduLazaro\Laractions\Tests\Support\TestAction;
use EduLazaro\Laractions\Tests\Support\ExtendedTestAction;
use EduLazaro\Laractions\Concerns\HasActions;


class TestModelEntity extends Model
{
    use HasActions;

    protected $table = 'test_entities';
    protected $guarded = [];

    protected $actions = [
        'test_action' => TestAction::class,
        'extended_test_action' => ExtendedTestAction::class,
    ];

    public string $name = "Test Entity";
}
