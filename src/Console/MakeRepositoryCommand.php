<?php

namespace Kamarkoding\KamarkodingRepository\Console;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;

class MakeRepositoryCommand extends Command
{
    protected $signature = 'make:repository {name}';
    protected $description = 'Create a repository interface and implementation';

    protected $files;

    public function __construct(Filesystem $files)
    {
        parent::__construct();
        $this->files = $files;
    }

    public function handle()
    {
        $name = Str::studly($this->argument('name'));

        $interfacePath = app_path("Repository/Contracts/{$name}RepositoryInterface.php");
        $classPath     = app_path("Repository/Eloquent/{$name}Repository.php");

        $this->makeDirectory($interfacePath);
        $this->makeDirectory($classPath);

        $this->createFromStub(
            $this->stub('repository-interface.stub'),
            $interfacePath,
            ['{{name}}' => $name]
        );

        $this->createFromStub(
            $this->stub('repository.stub'),
            $classPath,
            ['{{name}}' => $name]
        );

        $this->info("Repository created successfully.");
    }

    protected function stub(string $file)
    {
        return __DIR__ . '/../Stubs/' . $file;
    }

    protected function createFromStub(string $stubPath, string $targetPath, array $replace)
    {
        if (! $this->files->exists($stubPath)) {
            throw new \Exception("Stub not found: {$stubPath}");
        }

        if ($this->files->exists($targetPath)) {
            $this->warn("Skipped (already exists): {$targetPath}");
            return;
        }

        $content = str_replace(
            array_keys($replace),
            array_values($replace),
            $this->files->get($stubPath)
        );

        $this->files->put($targetPath, $content);

        $this->info("Created: Class " . basename($targetPath));
    }

    protected function makeDirectory($path)
    {
        $dir = dirname($path);

        if (!$this->files->isDirectory($dir)) {
            $this->files->makeDirectory($dir, 0755, true);
        }
    }
}
