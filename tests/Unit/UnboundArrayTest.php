<?php

namespace EduLazaro\Laractions\Tests\Unit;

use EduLazaro\Laractions\Tests\BaseTestCase;
use EduLazaro\Laractions\Tests\Support\DataAndOptionAction;
use EduLazaro\Laractions\Tests\Support\TestAction;

class UnboundArrayTest extends BaseTestCase
{
    public function test_it_explains_an_array_that_matches_no_parameter()
    {
        $this->expectException(\TypeError::class);
        $this->expectExceptionMessage('DataAndOptionAction::handle() received a single array whose keys (name, url) match none of its parameters ($data, $option), so $data was left empty. If the array is meant as one argument, pass it by name: run(data: [...]).');

        DataAndOptionAction::create()->run(['name' => 'Shop', 'url' => 'https://shop.example']);
    }

    public function test_the_array_passed_by_name_binds_as_one_argument()
    {
        $result = DataAndOptionAction::create()->run(data: ['name' => 'Shop']);

        $this->assertSame(['data' => ['name' => 'Shop'], 'option' => null], $result);
    }

    public function test_an_array_whose_keys_are_the_parameters_still_spreads()
    {
        $option = new \stdClass();

        $result = DataAndOptionAction::create()->run(['data' => ['name' => 'Shop'], 'option' => $option]);

        $this->assertSame(['data' => ['name' => 'Shop'], 'option' => $option], $result);
    }

    public function test_positional_arguments_are_untouched()
    {
        $result = DataAndOptionAction::create()->run(['name' => 'Shop'], null);

        $this->assertSame(['data' => ['name' => 'Shop'], 'option' => null], $result);
    }

    public function test_a_partial_match_keeps_the_existing_behaviour()
    {
        $this->expectException(\TypeError::class);
        $this->expectExceptionMessageMatches('/must be of type array, null given/');

        DataAndOptionAction::create()->run(['option' => new \stdClass()]);
    }

    public function test_an_action_with_rules_still_answers_with_its_validation_error()
    {
        $this->expectException(\Illuminate\Validation\ValidationException::class);

        TestAction::create()->run(['unrelated' => 'value']);
    }
}
