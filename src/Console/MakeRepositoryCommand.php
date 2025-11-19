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
        $rawName = $this->argument('name');
        $parsed = $this->parseName($rawName);

        $className       = $parsed['class'];          // Nama repository (User)
        $folderPath      = $parsed['path'];           // Folder (Admin)
        $namespaceSuffix = $parsed['namespacePath'];  // Admin

        // Tentukan lokasi file interface
        $interfacePath = app_path(
            $folderPath
                ? "Repository/Contracts/{$folderPath}/{$className}RepositoryInterface.php"
                : "Repository/Contracts/{$className}RepositoryInterface.php"
        );

        // Tentukan lokasi file repository class
        $classPath = app_path(
            $folderPath
                ? "Repository/Eloquent/{$folderPath}/{$className}Repository.php"
                : "Repository/Eloquent/{$className}Repository.php"
        );

        // Buat folder jika belum ada
        $this->makeDirectory($interfacePath);
        $this->makeDirectory($classPath);

        // Namespace final
        $interfaceNamespace = "App\\Repository\\Contracts" . ($namespaceSuffix ? "\\{$namespaceSuffix}" : "");
        $classNamespace     = "App\\Repository\\Eloquent"  . ($namespaceSuffix ? "\\{$namespaceSuffix}" : "");

        // Buat interface
        $this->createFromStub(
            $this->stub('repository-interface.stub'),
            $interfacePath,
            [
                '{{name}}'      => $className,
                '{{namespace}}' => $interfaceNamespace,
            ]
        );

        // Buat class repository
        $this->createFromStub(
            $this->stub('repository.stub'),
            $classPath,
            [
                '{{name}}'      => $className,
                '{{namespace}}' => $classNamespace,
            ]
        );

        $this->info("Repository created successfully.");
    }

    /**
     * Parsing "Admin/User" → folder = Admin, class = User
     */
    protected function parseName($name)
    {
        $name = str_replace('\\', '/', $name);

        $segments = explode('/', $name);

        $class = array_pop($segments); // Nama class
        $path  = implode('/', $segments); // Folder

        return [
            'class'         => Str::studly($class),
            'path'          => $path,
            'namespacePath' => str_replace('/', '\\', $path),
        ];
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

        $this->info("Created: " . basename($targetPath));
    }

    protected function makeDirectory($path)
    {
        $dir = dirname($path);

        if (! $this->files->isDirectory($dir)) {
            $this->files->makeDirectory($dir, 0755, true);
        }
    }
}
