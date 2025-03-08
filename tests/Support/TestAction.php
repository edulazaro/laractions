<?php

namespace EduLazaro\Laractions\Tests\Support;

use EduLazaro\Laractions\Action;
use Illuminate\Validation\ValidationException;

class TestAction extends Action
{
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
}
