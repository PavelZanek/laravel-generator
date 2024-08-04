<?php

declare(strict_types=1);

namespace PavelZanek\LaravelGenerator\Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Orchestra\Testbench\TestCase;
use PavelZanek\LaravelGenerator\LaravelGeneratorServiceProvider;

class OrchestraTestCase extends TestCase
{
    use RefreshDatabase;

    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        // Setup default database to use sqlite :memory:
        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
    }

    /**
     * Get package providers.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return array
     */
    protected function getPackageProviders($app): array
    {
        return [
            LaravelGeneratorServiceProvider::class,
        ];
    }
}
