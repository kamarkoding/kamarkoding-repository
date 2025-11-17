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
    protected $basePath;

    public function __construct(Filesystem $files)
    {
        parent::__construct();
        $this->files = $files;
        $this->basePath = app_path('Repository');
    }

    public function handle()
    {
        $name = Str::studly($this->argument('name'));

        $this->createFolders();
        $this->createInterface($name);
        $this->createRepository($name);

        $this->info("Repository {$name} created!");
    }

    protected function createFolders()
    {
        $paths = [
            "{$this->basePath}/Contracts",
            "{$this->basePath}/Eloquent",
        ];

        foreach ($paths as $path) {
            if (!$this->files->exists($path)) {
                $this->files->makeDirectory($path, 0755, true);
            }
        }
    }

    protected function createInterface($name)
    {
        $stub = $this->getStub('repository-interface');

        $content = str_replace(
            ['DummyNamespace', 'DummyClassInterface'],
            ['App\\Repository', "{$name}RepositoryInterface"],
            $stub
        );

        $file = "{$this->basePath}/Contracts/{$name}RepositoryInterface.php";

        $this->files->put($file, $content);
    }

    protected function createRepository($name)
    {
        $stub = $this->getStub('repository');

        $content = str_replace(
            ['DummyNamespace', 'DummyClass'],
            ['App\\Repository', "{$name}Repository"],
            $stub
        );

        $file = "{$this->basePath}/Eloquent/{$name}Repository.php";

        $this->files->put($file, $content);
    }

    protected function getStub($path)
    {
        return $this->files->get(__DIR__."/../../Stubs/{$path}.stub");
    }
}
