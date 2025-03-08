<?php

namespace EduLazaro\Laractions\Tests\Unit;

use EduLazaro\Laractions\Tests\Support\TestAction;
use EduLazaro\Laractions\Tests\Support\TestEntity;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class ActionTest extends TestCase
{
    /** @test */
    public function it_can_create_an_action_instance()
    {
        $action = TestAction::create();
        $this->assertInstanceOf(TestAction::class, $action);
    }

    /** @test */
    public function it_can_associate_an_entity_with_for_method()
    {
        $action = TestAction::create();
        $entity = new TestEntity();

        $action->for($entity);

        $this->assertSame($entity, $action->getActionable());
    }

    /** @test */
    public function it_throws_exception_if_for_method_receives_non_object()
    {
        $this->expectException(\TypeError::class);
    
        $action = TestAction::create();
        $action->for("not-an-object");
    }

    /** @test */
    public function it_can_inject_properties_with_with_method_and_array_params()
    {
        $action = TestAction::create()->with(['foo' => 'bar']);
        $this->assertEquals('bar', $action->foo);
    }

    /** @test */
    public function it_can_inject_properties_with_with_method_and_named_params()
    {
        $action = TestAction::create()->with(foo: 'bar');
        $this->assertEquals('bar', $action->foo);
    }

    /** @test */

    public function it_validates_input_parameters_before_execution()
    {
        $this->expectException(ValidationException::class);

        $action = new TestAction();
        $action->run(['invalid_key' => 'value']);

    }

    /** @test */
    /*
    public function it_logs_messages_correctly()
    {
        Log::shouldReceive('info')
            ->once()
            ->with('[Action: EduLazaro\Laractions\Tests\Support\TestAction] Test log message', ['key' => 'value']);

        $action = new TestAction();
        $this->invokeMethod($action, 'log', ['Test log message', ['key' => 'value']]);
    }
        */

    /** @test */
    /*
    public function it_executes_the_action()
    {
        $action = new TestAction();
        $result = $action->run(['required_key' => 'valid']);

        $this->assertEquals("Executed with valid", $result);
    }
        */

    /** Helper to invoke protected/private methods in tests */
    /*
    protected function invokeMethod(&$object, $methodName, array $parameters = [])
    {
        $reflection = new \ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $parameters);
    }
    */
}
