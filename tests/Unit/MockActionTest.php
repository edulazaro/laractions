<?php

namespace EduLazaro\Laractions\Tests\Unit;

use EduLazaro\Laractions\Tests\BaseTestCase;
use EduLazaro\Laractions\Tests\Support\TestAction;
use EduLazaro\Laractions\Tests\Support\TestModelEntity;

class MockActionTest extends BaseTestCase
{
    protected function doble(): object
    {
        return new class {
            public function on($model)
            {
                return $this;
            }

            public function run(...$params)
            {
                return 'mocked';
            }
        };
    }

    public function test_it_returns_the_mock_instead_of_running_the_real_action()
    {
        $entity = TestModelEntity::create();

        $entity->mockAction(TestAction::class, $this->doble());

        $result = $entity->action(TestAction::class)
            ->run(['name' => 'Bob', 'email' => 'bob@example.com']);

        $this->assertSame('mocked', $result);
    }

    public function test_it_accepts_a_mock_that_does_not_implement_on()
    {
        $entity = TestModelEntity::create();

        $entity->mockAction(TestAction::class, new class {
            public function run(...$params)
            {
                return 'mocked';
            }
        });

        $this->assertSame('mocked', $entity->action(TestAction::class)->run());
    }

    public function test_it_only_mocks_the_instance_it_was_set_on()
    {
        $mocked = TestModelEntity::create();
        $untouched = TestModelEntity::create();

        $mocked->mockAction(TestAction::class, $this->doble());

        $params = ['name' => 'Bob', 'email' => 'bob@example.com'];

        $this->assertSame('mocked', $mocked->action(TestAction::class)->run($params));
        $this->assertSame('Bob_bob@example.com', $untouched->action(TestAction::class)->run($params));
    }
}
