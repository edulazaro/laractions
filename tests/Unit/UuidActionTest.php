<?php

namespace EduLazaro\Laractions\Tests\Unit;

use EduLazaro\Laractions\Tests\BaseTestCase;
use EduLazaro\Laractions\Tests\Support\UuidTestAction;
use EduLazaro\Laractions\Tests\Support\UuidTestEntity;
use EduLazaro\Laractions\Jobs\ActionJob;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Queue;

class UuidActionTest extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        UuidTestAction::$handledFor = null;
    }

    public function test_it_can_dispatch_an_action_bound_to_a_model_with_a_string_key()
    {
        Queue::fake();

        $entity = UuidTestEntity::create(['name' => 'Uuid entity']);

        $entity->action(UuidTestAction::class)->dispatch(['name' => 'Alice']);

        Queue::assertPushed(ActionJob::class, function ($job) use ($entity) {
            return $job->actionableType === UuidTestEntity::class
                && $job->actionableId === $entity->getKey();
        });
    }

    public function test_it_rehydrates_a_model_with_a_string_key_when_the_job_runs()
    {
        Queue::fake();

        $entity = UuidTestEntity::create(['name' => 'Uuid entity']);

        $entity->action(UuidTestAction::class)->dispatch(['name' => 'Alice']);

        Queue::assertPushed(ActionJob::class, function ($job) {
            $job->handle();
            return true;
        });

        $this->assertSame($entity->getKey(), UuidTestAction::$handledFor);
    }

    public function test_it_fails_with_a_clear_error_when_the_actionable_no_longer_exists()
    {
        Queue::fake();

        $entity = UuidTestEntity::create(['name' => 'Uuid entity']);

        $entity->action(UuidTestAction::class)->dispatch(['name' => 'Alice']);

        $dispatched = null;

        Queue::assertPushed(ActionJob::class, function ($job) use (&$dispatched) {
            $dispatched = $job;
            return true;
        });

        $entity->delete();

        $this->expectException(ModelNotFoundException::class);

        $dispatched->handle();
    }
}
