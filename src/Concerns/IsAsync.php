<?php

namespace EduLazaro\Laractions\Concerns;

use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

/**
 * Trait IsAsync
 *
 * Marks an action as supporting asynchronous execution.
 * This provides queue-related configurations but does NOT enforce dispatching.
 */
trait IsAsync
{
    use Dispatchable, InteractsWithQueue, Queueable;

    /** @var int Number of times the action should be retried on failure */
    protected int $tries = 1;

    /** @var int Delay in seconds before the job executes */
    protected int $delay = 0;

    /** @var string|null The queue where the action should be dispatched */
    protected ?string $queue = null;

    /** @var int|null Maximum execution time (in seconds) */
    protected ?int $timeout = null;

    /** @var bool Whether logging is enabled */
    protected bool $loggingEnabled = false;

    /**
     * Set the number of retries for the async action.
     *
     * @param int $times Number of retries.
     * @return static
     */
    public function retry(int $times): static
    {
        $this->tries = $times;
        return $this;
    }

    /**
     * Set a delay before the async action executes.
     *
     * @param int $seconds Delay in seconds.
     * @return static
     */
    public function delay(int $seconds): static
    {
        $this->delay = $seconds;
        return $this;
    }

    /**
     * Set the execution timeout for the async action.
     *
     * @param int $seconds Timeout in seconds.
     * @return static
     */
    public function timeout(int $seconds): static
    {
        $this->timeout = $seconds;
        return $this;
    }

    /**
     * Enable or disable logging when the action is dispatched.
     *
     * @param bool $enabled Whether logging should be enabled.
     * @return static
     */
    public function log(bool $enabled = true): static
    {
        $this->loggingEnabled = $enabled;
        return $this;
    }

    /**
     * Log failed job execution.
     *
     * @param \Throwable $exception The exception that caused failure.
     * @return void
     */
    public function failed(\Throwable $exception): void
    {
        Log::error("[AsyncAction: " . static::class . "] Job failed: " . $exception->getMessage(), [
            'exception' => $exception->getTraceAsString(),
        ]);
    }

    /**
     * Set the queue where this action should be dispatched.
     *
     * @param string $queueName Queue name.
     * @return static
     */
    public function queue(string $queueName): static
    {
        $this->queue = $queueName;
        return $this;
    }

    /**
     * Dispatch the action asynchronously with configured options.
     *
     * @return void
     */
    public function dispatch(): void
    {
        if ($this->loggingEnabled) {
            Log::info("[AsyncAction: " . static::class . "] Dispatching job.", [
                'queue' => $this->queue,
                'delay' => $this->delay,
                'tries' => $this->tries,
            ]);
        }

        dispatch($this->onQueue($this->queue)->delay($this->delay)->tries($this->tries));
    }

    /**
     * Cancel the job manually (only works for queued but not yet executed jobs).
     *
     * @return void
     */
    public function cancel(): void
    {
        if (method_exists($this, 'delete')) {
            $this->delete();
            Log::info("[AsyncAction: " . static::class . "] Job cancelled.");
        } else {
            Log::warning("[AsyncAction: " . static::class . "] Unable to cancel: Job does not support delete().");
        }
    }

    /**
     * Log successful job execution.
     *
     * @return void
     */
    public function completed(): void
    {
        Log::info("[AsyncAction: " . static::class . "] Job completed successfully.");
    }


    /**
     * Get the status of the job.
     *
     * @return string The job status.
     */
    public function status(): string
    {
        if (!property_exists($this, 'job') || !$this->job) {
            return 'not queued';
        }

        if ($this->job->isReleased()) {
            return 'retrying';
        }

        if ($this->job->isDeleted()) {
            return 'cancelled';
        }

        if ($this->job->hasFailed()) {
            return 'failed';
        }

        return 'queued';
    }
}
