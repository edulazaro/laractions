<?php

namespace EduLazaro\Laractions\Tests\Unit;

use EduLazaro\Laractions\Tests\BaseTestCase;
use EduLazaro\Laractions\Tests\Support\BagAction;
use EduLazaro\Laractions\Tests\Support\SingleScalarAction;
use EduLazaro\Laractions\Tests\Support\TestAction;

class BagActionTest extends BaseTestCase
{
    /** @test */
    public function it_passes_a_single_associative_array_as_the_bag_to_a_single_array_param()
    {
        $action = BagAction::create();

        $result = $action->run(['concept' => 'x', 'amount' => 10]);

        $this->assertSame(['concept' => 'x', 'amount' => 10], $result);
    }

    /** @test */
    public function it_still_accepts_a_single_array_param_via_real_named_argument()
    {
        $action = BagAction::create();

        $result = $action->run(attributes: ['concept' => 'x']);

        $this->assertSame(['concept' => 'x'], $result);
    }

    /** @test */
    public function it_keeps_an_indexed_array_whole_for_a_single_array_param()
    {
        $action = BagAction::create();

        $result = $action->run([['a'], ['b']]);

        $this->assertSame([['a'], ['b']], $result);
    }

    /** @test */
    public function it_still_maps_array_keys_by_name_when_handle_has_several_params()
    {
        $action = TestAction::create();

        $result = $action->run(['name' => 'John', 'email' => 'john@example.com']);

        $this->assertSame('John_john@example.com', $result);
    }

    /** @test */
    public function it_forwards_a_scalar_positionally_to_a_single_param()
    {
        $action = SingleScalarAction::create();

        $result = $action->run('hello');

        $this->assertSame('hello', $result);
    }
}
