<?php

namespace EduLazaro\Laractions\Console\Commands;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Str;

class MakeActionCommand extends GeneratorCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'make:action {name} {--model=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new Action class';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Action';

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return $this->option('model')
            ? __DIR__ . '/stubs/model-action.stub'
            : __DIR__ . '/stubs/action.stub';
    }

    /**
     * Get the default namespace for the class.
     *
     * @param string $rootNamespace
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace)
    {
        if ($this->option('model')) {
            $modelFullName = trim($this->option('model'), '\\');
            $modelShortName = class_basename($modelFullName);

            return "{$rootNamespace}\\Actions\\{$modelShortName}";
        }

        return "{$rootNamespace}\\Actions";
    }

    /**
     * Replace placeholders inside the stub file.
     *
     * @param string $stub
     * @param string $name
     * @return string
     */
    protected function replaceClass($stub, $name)
    {
        $actionClass = class_basename($name);
        $namespace = $this->getNamespace($name);

        if ($this->option('model')) {
            $modelFullName = trim($this->option('model'), '\\');

            // If no namespace is provided, assume it's under App\Models
            if (!str_contains($modelFullName, '\\')) {
                $modelFullName = 'App\\Models\\' . $modelFullName;
            }

            // Extract base model class name
            $modelName = class_basename($modelFullName);
            $modelVariable = lcfirst($modelName);
        } else {
            $modelFullName = null;
            $modelName = null;
            $modelVariable = null;
        }

        return str_replace(
            ['{{ namespace }}', '{{ class }}', '{{ modelFullName }}', '{{ modelName }}', '{{ modelVariable }}'],
            [$namespace, $actionClass, $modelFullName ?? '', $modelName ?? '', $modelVariable ?? ''],
            $stub
        );
    }

    /**
     * Get the correct file path where the action should be created.
     *
     * @param string $name
     * @return string
     */
    protected function getPath($name)
    {
        // Remove the root namespace (e.g., "App\") from the class name
        $name = Str::replaceFirst($this->rootNamespace(), '', $name);
        $name = str_replace('\\', '/', $name);

        // Standard actions go directly inside "app/Actions/"
        if (!$this->option('model')) {
            return app_path("{$name}.php");
        }

        // Model-based actions go inside "app/Actions/ModelShortName/"
        $modelFullName = trim($this->option('model'), '\\');
        $modelShortName = class_basename($modelFullName);

        return app_path("Actions/{$modelShortName}/" . class_basename($name) . ".php");
    }
}
