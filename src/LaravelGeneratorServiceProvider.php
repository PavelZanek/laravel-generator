<?php

declare(strict_types=1);

namespace PavelZanek\LaravelGenerator;

use Illuminate\Foundation\Console\AboutCommand;
use Illuminate\Support\ServiceProvider;
use PavelZanek\LaravelGenerator\Console\Commands\CopyGuestSectionCommand;
use PavelZanek\LaravelGenerator\Console\Commands\CopyStubsCommand;
use PavelZanek\LaravelGenerator\Console\Commands\GenerateAppSectionCommand;
use PavelZanek\LaravelGenerator\Console\Commands\GenerateGuestSectionCommand;
use PavelZanek\LaravelGenerator\Console\Commands\CopyAppSectionCommand;

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
        AboutCommand::add('My Package', fn () => ['Version' => '1.0.0']);

        // Publish the configuration file
        $this->publishes([
            __DIR__.'/../config/laravel-generator.php' => config_path('laravel-generator.php'),
        ], 'config');

        if ($this->app->runningInConsole()) {
            // Publish stubs
            $this->publishes([
                __DIR__.'/../stubs' => resource_path('stubs/vendor/laravel-generator'),
            ], 'stubs');

            // Register commands
            $this->commands([
                CopyStubsCommand::class,
                GenerateAppSectionCommand::class,
                GenerateGuestSectionCommand::class,
                CopyAppSectionCommand::class,
                CopyGuestSectionCommand::class,
            ]);
        }
    }
}
