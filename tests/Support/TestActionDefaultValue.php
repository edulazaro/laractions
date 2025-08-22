<?php

namespace EduLazaro\Laractions\Tests\Support;

use EduLazaro\Laractions\Action;
use EduLazaro\Laractions\Tests\Support\TestEntity;

class TestActionDefaultValue extends Action
{
    protected TestEntity $testEntity;
    public string $foo = '';

    protected array $rules = [
        'name' => 'required|string',
        'email' => 'required|string|email',
    ];

    public function getActionable(): ?object
    {
        return $this->actionable;
    } 

    protected function handle(string $name, string $email = 'bob@example.com'): string
    {
        return $name . '_' . $email;
    }

    public function getEntity()
    {
        return $this->testEntity;
    }
}
