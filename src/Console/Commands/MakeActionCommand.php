
    <?php

    namespace EduLazaro\Laractions\Console\Commands;
    
    use Illuminate\Console\Command;
    use Illuminate\Filesystem\Filesystem;
    
    class MakeActionCommand extends Command
    {
        protected $signature = 'make:action {name} {--model=} {--async}';
    
        protected $description = 'Create a new Action class';
    
        public function handle()
        {
            $name = $this->argument('name');
            $model = $this->option('model');
            $isAsync = $this->option('async');
    
            $namespace = 'App\\Actions';
            $class = class_basename($name);
            $modelNamespace = $model ? "App\\Models\\$model" : null;
            $modelVariable = $model ? lcfirst(class_basename($model)) : null;
    
            $stubDirectory = base_path('app/Console/Commands/stubs/');
    
            if ($model) {
                $stubFile = $isAsync ? 'async-model-action.stub' : 'model-action.stub';
                $namespace .= "\\$model"; // Change namespace to include model folder
                $folderPath = app_path("Actions/{$model}"); // Actions inside model-based folders
            } else {
                $stubFile = $isAsync ? 'async-action.stub' : 'action.stub';
                $folderPath = app_path("Actions"); // General actions folder
            }
    
            $stubPath = $stubDirectory . $stubFile;
    
            if (!file_exists($stubPath)) {
                $this->error("Stub file not found: $stubPath");
                return;
            }
    
            $stub = file_get_contents($stubPath);
            $stub = str_replace('{{ namespace }}', $namespace, $stub);
            $stub = str_replace('{{ class }}', $class, $stub);
            $stub = str_replace('{{ model }}', $modelNamespace ?? '', $stub);
            $stub = str_replace('{{ modelVariable }}', $modelVariable ?? '', $stub);
    
            // Define file path
            $path = "{$folderPath}/{$class}.php";
    
            if (file_exists($path)) {
                $this->error("Action already exists: $path");
                return;
            }
    
            (new Filesystem)->ensureDirectoryExists($folderPath);
            file_put_contents($path, $stub);
    
            $this->info("Action created successfully: $path");
        }
    }    
}
