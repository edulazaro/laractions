<?php

namespace EduLazaro\Laractions\Tests\Unit;

use EduLazaro\Laractions\Tests\BaseTestCase;
use EduLazaro\Laractions\Tests\Support\TestAction;
use EduLazaro\Laractions\Tests\Support\ExtendedTestAction;
use EduLazaro\Laractions\Tests\Support\MultiplyNumbersAction;
use EduLazaro\Laractions\Tests\Support\TestEntity;
use Illuminate\Validation\ValidationException;


class ActionTest extends BaseTestCase
{
    /** @test */
    public function it_can_create_an_action_instance()
    {
        $action = TestAction::create();
        $this->assertInstanceOf(TestAction::class, $action);
    }

    /** @test */
    public function it_can_run_a_standalone_action_with_valid_data()
    {
        $action = TestAction::create();
        $result = $action->run(['name' => 'John Doe', 'email' => 'john@example.com']);

        $this->assertEquals('John Doe_john@example.com', $result);
    }

    /** @test */
    public function it_throws_validation_exception_for_invalid_data()
    {
        $this->expectException(ValidationException::class);

        $action = TestAction::create();
        $action->run(['name' => 'John Doe', 'email' => 'invalid-email']);
    }

    /** @test */
    public function it_can_validate_and_run_multiply_numbers_action()
    {
        $action = MultiplyNumbersAction::create();
        $result = $action->run(['a' => 5, 'b' => 3]);

        $this->assertEquals(15, $result);
    }

    /** @test */
    public function it_throws_validation_exception_for_invalid_multiply_numbers_action()
    {
        $this->expectException(ValidationException::class);

        $action = MultiplyNumbersAction::create();
        $action->run(['a' => 'five', 'b' => 3]); // 'a' is not numeric
    }

    /** @test */
    public function it_sets_and_gets_an_actionable_entity()
    {
        $entity = new TestEntity();
        $action = TestAction::create()->for($entity);

        $this->assertSame($entity, $action->getActionable());
    }

    /** @test */
    public function it_can_set_parameters_using_with_method()
    {
        $action = TestAction::create();
        $action->with(['foo' => 'bar']);

        $this->assertEquals('bar', $action->foo);
    }

    /** @test */
    public function it_can_set_parameters_using_with_method_named_params()
    {
        $action = TestAction::create();
        $action->with(foo: 'bar');

        $this->assertEquals('bar', $action->foo);
    }


    /** @test */
    public function it_can_execute_action_using_action_method_with_class()
    {
        $entity = new TestEntity();

        $action = $entity->action(TestAction::class);
        $result = $action->run(['name' => 'Alice', 'email' => 'alice@example.com']);

        $this->assertEquals('Alice_alice@example.com', $result);
    }

    /** @test */
    public function it_can_execute_action_using_action_method_with_key()
    {
        $entity = new TestEntity();
        $result = $entity->action('test_action')->run(['name' => 'Bob', 'email' => 'bob@example.com']);

        $this->assertEquals('Bob_bob@example.com', $result);
    }

        /** @test */
        public function it_can_execute_action_using_action_method_with_key_on_extended_action()
        {
            $entity = new TestEntity();
            $result = $entity->action('extended_test_action')->run(['name' => 'Bob', 'email' => 'bob@example.com']);
    
            $this->assertEquals('Bob_bob@example.com_ok', $result);
        }

    /** @test */
    public function it_can_execute_action_using_action_method_with_named_params()
    {
        $entity = new TestEntity();

        $action = $entity->action('test_action');
        $result = $action->run(name: 'Bob', email: 'bob@example.com');

        $this->assertEquals('Bob_bob@example.com', $result);
    }

    /** @test */
    public function it_can_resolve_dynamic_actionable_name()
    {
        $entity = new TestEntity();
        $action = TestAction::create()->for($entity);
        
        $this->assertSame($entity, $action->getEntity());
    }

    /** @test */
    public function it_can_resolve_dynamic_actionable_name_in_extended_action()
    {
        $entity = new TestEntity();
        $action = ExtendedTestAction::create()->for($entity);
        
        $this->assertSame($entity, $action->getEntity());
    }
}
