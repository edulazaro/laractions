<?php

namespace EduLazaro\Laractions\Tests\Unit;

use EduLazaro\Laractions\Tests\BaseTestCase;
use EduLazaro\Laractions\Tests\Support\TestAction;
use EduLazaro\Laractions\Tests\Support\TestModelEntity;
use EduLazaro\Laractions\Tests\Support\ExtendedTestAction;
use EduLazaro\Laractions\Tests\Support\MultiplyNumbersAction;
use EduLazaro\Laractions\Tests\Support\TestEntity;
use Illuminate\Validation\ValidationException;
use EduLazaro\Laractions\ActionTrace;
use Illuminate\Support\Facades\Schema;

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
        $action = TestAction::create()->on($entity);

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
        $action = TestAction::create()->on($entity);

        $this->assertSame($entity, $action->getEntity());
    }

    /** @test */
    public function it_can_resolve_dynamic_actionable_name_in_extended_action()
    {
        $entity = new TestEntity();
        $action = ExtendedTestAction::create()->on($entity);

        $this->assertSame($entity, $action->getEntity());
    }

    /** @test */
    public function it_can_resolve_actions_in_model_actions_array()
    {
        $entity = new TestEntity();
        $result =  $entity->action('test_action')->run(['name' => 'Bob', 'email' => 'bob@example.com']);

        $this->assertEquals('Bob_bob@example.com', $result);
    }

    /** @test */
    public function it_can_resolve_actions_in_model_actions_array_with_named_params()
    {
        $entity = new TestEntity();
        $result =  $entity->action('test_action')->run(name: 'Bob', email: 'bob@example.com');
    
        $this->assertEquals('Bob_bob@example.com', $result);
    }

    /** @test */
    public function it_can_resolve_actions_in_model_actions_array_with_standard_params()
    {
        $entity = new TestEntity();
        $result =  $entity->action('test_action')->run( 'Bob', 'bob@example.com');
    
        $this->assertEquals('Bob_bob@example.com', $result);
    }

    /** @test */
    public function it_does_not_trace_unless_explicitly_enabled()
    {
        $entity = TestModelEntity::create();

        $entity->action('test_action')->run(['name' => 'Silent', 'email' => 'no-trace@example.com']);

        $this->assertDatabaseMissing('action_traces', [
            'action' => TestAction::class,
        ]);
    }

    /** @test */
    public function it_traces_action_when_trace_is_enabled()
    {
        $entity = TestModelEntity::create();

        $entity->action('test_action')
            ->trace()
            ->run(['name' => 'Traced', 'email' => 'yes@example.com']);

        $this->assertDatabaseHas('action_traces', [
            'action' => TestAction::class,
        ]);
    }

    /** @test */
    public function it_stores_actor_and_target_when_tracing()
    {
        $entity = TestModelEntity::create();
    
        $user = new class {
            public function getMorphClass() { return 'user'; }
            public function getKey() { return 123; }
        };
    
        $entity->action('test_action')
            ->trace()
            ->actor($user)
            ->on($entity)
            ->run(['name' => 'Traced', 'email' => 'yes@example.com']);
    
        $trace = ActionTrace::latest()->first();
    
        $this->assertEquals('user', $trace->actor_type);
        $this->assertEquals(123, $trace->actor_id);
        $this->assertEquals(get_class($entity), $trace->target_type);
        $this->assertEquals($entity->id, $trace->target_id); // optional but precise
    }
}
