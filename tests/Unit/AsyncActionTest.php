<?php

namespace EduLazaro\Laractions\Tests\Unit;

use EduLazaro\Laractions\Tests\BaseTestCase;
use EduLazaro\Laractions\Tests\Support\AsyncTestAction;
use EduLazaro\Laractions\Tests\Support\TestEntity;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Queue\Events\JobFailed;
use EduLazaro\Laractions\Jobs\ActionJob;
use Illuminate\Validation\ValidationException;
use Throwable;

class AsyncActionTest extends BaseTestCase
{
    use WithFaker;

    /** @test */
    public function it_can_dispatch_and_execute_an_async_action()
    {
        Queue::fake();

        $action = AsyncTestAction::create()->dispatch(['name' => 'Alice', 'email' => 'alicex@example.com']);

        Queue::assertPushed(ActionJob::class, function ($job) use ($action) {
            return $job->action instanceof AsyncTestAction;
        });

        Queue::assertPushed(ActionJob::class);
        Queue::assertPushed(ActionJob::class, function ($job) {
            $job->handle();
            return true;
        });
    }

    /** @test */
    public function it_logs_failed_job_execution()
    {
        Log::shouldReceive('error')
            ->once()
            ->withArgs(fn($message) => str_contains($message, 'Job failed'));

        // Create an instance of ActionJob with a sample action
        $action = AsyncTestAction::create();
        $job = new \EduLazaro\Laractions\Jobs\ActionJob($action, ['name' => 'Alice', 'email' => 'alice@example.com']);

        // Manually call the failed() method
        $job->failed(new \Exception('Test failure'));
    }

    /** @test */
    public function it_can_set_queue_and_delay()
    {
        Queue::fake();

        $action = AsyncTestAction::create();
        $action->queue('high')->delay(30)->dispatch(['name' => 'Alice', 'email' => 'alice@example.com']);

        Queue::assertPushed(ActionJob::class, function ($job) {
            return $job->queue === 'high' && $job->delay === 30;
        });
    }

    /** @test */
    public function it_throws_validation_exception_for_invalid_data()
    {
        $this->expectException(ValidationException::class);

        $action = AsyncTestAction::create();
        $action->dispatch(['name' => 'Alice', 'email' => 'invalid-email']);
    }

    /** @test */
    public function it_can_retry_failed_jobs()
    {
        Queue::fake();

        $action = AsyncTestAction::create()->retry(3);
        $action->dispatch(['name' => 'Alice', 'email' => 'alice@example.com']);

        Queue::assertPushed(ActionJob::class, function ($job) {
            return $job->tries === 3;
        });
    }
}
