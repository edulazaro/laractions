<?php

namespace EduLazaro\Laractions\Tests\Support;

use EduLazaro\Laractions\Action;
use EduLazaro\Laractions\Tests\Support\UuidTestEntity;

class UuidTestAction extends Action
{
    /** @var string|null Key of the entity bound to the last run, for assertions */
    public static ?string $handledFor = null;

    protected UuidTestEntity $uuidTestEntity;

    protected array $rules = [
        'name' => 'required|string',
    ];

    public function getEntity(): ?UuidTestEntity
    {
        return $this->uuidTestEntity ?? null;
    }

    protected function handle(string $name): string
    {
        static::$handledFor = $this->uuidTestEntity->getKey();

        return $name;
    }
}
