<?php

declare(strict_types=1);

namespace PavelZanek\LaravelGenerator;

use Illuminate\Foundation\Console\AboutCommand;
use Illuminate\Support\ServiceProvider;
use PavelZanek\LaravelGenerator\Console\Commands\CopyPackageFilesCommand;
use PavelZanek\LaravelGenerator\Console\Commands\GenerateAppSectionCommand;
use PavelZanek\LaravelGenerator\Console\Commands\GenerateGuestSectionCommand;

class LaravelGeneratorServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Merge user's config with the default config
        $this->mergeConfigFrom(
            __DIR__.'/../config/laravel-generator.php',
            'laravel-generator'
        );
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        AboutCommand::add('Laravel Generator', fn () => ['Version' => '0.2.0']);

        // Publish the configuration file
        $this->publishes([
            __DIR__.'/../config/laravel-generator.php' => config_path('laravel-generator.php'),
        ], 'laravel-generator-config');

        // Publish the view files
        $this->loadViewsFrom(__DIR__.'/../views', 'laravel-generator');
        $this->publishes([
            __DIR__.'/../views' => resource_path('views/vendor/laravel-generator'),
        ], 'laravel-generator-views');

        // Publish stubs
        $this->publishes([
            __DIR__.'/../stubs' => resource_path('stubs/vendor/laravel-generator'),
        ], 'laravel-generator-stubs');

        // Register commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                GenerateAppSectionCommand::class,
                GenerateGuestSectionCommand::class,
                CopyPackageFilesCommand::class,
            ]);
        }
    }
}
