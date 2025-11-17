<?php

namespace Kamarkoding\KamarkodingRepository\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use Kamarkoding\KamarkodingRepository\Console\MakeRepositoryCommand;

class RepositoryServiceProvider extends ServiceProvider
{
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                MakeRepositoryCommand::class,
            ]);
        }

        $this->autoBindRepositories();
    }

    protected function autoBindRepositories()
    {
        $filesystem   = new Filesystem();
        $contractPath = app_path('Repository/Contracts');
        $eloquentPath = app_path('Repository/Eloquent');

        if (!$filesystem->exists($contractPath) || !$filesystem->exists($eloquentPath)) {
            return;
        }

        foreach ($filesystem->files($contractPath) as $contract) {
            $interfaceName = pathinfo($contract->getFilename(), PATHINFO_FILENAME);

            $interfaceClass = "App\\Repository\\Contracts\\{$interfaceName}";
            $className = Str::replaceLast('Interface', '', $interfaceName);
            $implementationClass = "App\\Repository\\Eloquent\\{$className}";

            if (class_exists($implementationClass)) {
                $this->app->bind($interfaceClass, $implementationClass);
            }
        }
    }
}
