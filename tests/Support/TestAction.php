<?php

namespace EduLazaro\Laractions\Tests\Support;

use EduLazaro\Laractions\Action;
use EduLazaro\Laractions\Concerns\HasActions;
use Illuminate\Validation\ValidationException;
use EduLazaro\Laractions\Tests\Support\TestEntity;

class TestAction extends Action
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

    protected function handle(string $name, string $email): string
    {
        return $name . '_' . $email;
    }

    public function getEntity()
    {
        return $this->testEntity;
    }
}
