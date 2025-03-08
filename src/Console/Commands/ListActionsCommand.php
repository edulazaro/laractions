<?php

namespace EduLazaro\Laractions\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class ListActionsCommand extends Command
{
    protected $signature = 'list:actions';
    protected $description = 'List all registered actions, including subfolders';

    public function handle()
    {
        $actionsPath = app_path('Actions');

        if (!File::exists($actionsPath)) {
            $this->error("No actions found in $actionsPath");
            return;
        }

        // Recursively scan subdirectories
        $files = File::allFiles($actionsPath);

        if (empty($files)) {
            $this->warn("No action files found in $actionsPath.");
            return;
        }

        $this->info("Available Actions:");

        foreach ($files as $file) {
            $relativePath = str_replace([$actionsPath, '/', '.php'], ['', '\\', ''], $file->getRealPath());
            $actionClass = "App\\Actions{$relativePath}";

            $this->line("- $actionClass");
        }
    }
}