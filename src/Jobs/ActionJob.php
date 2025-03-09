<?php

namespace EduLazaro\Laractions\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use EduLazaro\Laractions\Action;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Throwable;

class ActionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    /**  @var Action|null The action instance if not using a model */
    public ?Action $action;

    /** @var string The fully qualified class name of the action  */
    public string $actionClass;

    /**  @var string|null The class name of the actionable model, if applicable */
    public ?string $actionableType = null;

    /** @var int|null The primary key of the actionable model, if applicable */
    public ?int $actionableId = null;

    /**  @var array The parameters passed to the action  */
    public array $params = [];

    /** @var int Number of times the job should retry */
    public int $tries = 1;
    

    /**
     * Create a new job instance.
     *
     * @param Action $action The action instance to be executed.
     * @param array $params The parameters passed to the action.
     */
    public function __construct(Action $action, $params = [])
    {
        $this->actionClass = get_class($action);
        $this->params = $params;
        $this->tries = $action->getTries();

        if ($action->getActionable() instanceof Model) {
            $this->actionableType = get_class($action->getActionable());
            $this->actionableId = $action->getActionable()->getKey();
        } else {
            $this->action = $action;
        }
    }

    /**
     * Set the parameters for the job execution.
     *
     * @param array $params The parameters to set.
     * @return void
     */
    public function setParams(array $params = [])
    {
        $this->params = $params;
    }

    /**
     * Handle a job failure.
     *
     * @param Throwable $exception The exception that caused the job to fail.
     * @return void
     */
    public function failed(Throwable $exception): void
    {
        Log::error("[ActionJob: " . static::class . "] Job failed: " . $exception->getMessage(), [
            'exception' => $exception->getTraceAsString(),
        ]);
    }

    /**
     * Run the job.
     *
     * @return void
     */
    public function handle()
    {
        if ($this->action) {
            $this->action->run($this->params);
        } else if ($this->actionableType && $this->actionableId) {
            $action = app($this->actionClass);
            $action->for($this->actionableType::find($this->actionableId)); 
        }
    }
}
