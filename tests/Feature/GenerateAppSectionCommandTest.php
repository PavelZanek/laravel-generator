<?php

declare(strict_types=1);

use Illuminate\Console\Command;
use PavelZanek\LaravelGenerator\Console\Commands\GenerateAppSectionCommand;
use PavelZanek\LaravelGenerator\Enums\FrameworkEnum;
use PavelZanek\LaravelGenerator\Enums\TestingFrameworkEnum;

it(description: 'tests the GenerateAppSection command', closure: function (): void {
    $this->artisan(GenerateAppSectionCommand::class)
        ->expectsQuestion('What model do you want to generate App files for?', 'ExampleClass')
        ->expectsQuestion('What framework do you want to use?', FrameworkEnum::LIVEWIRE->value)
        ->expectsQuestion('Want to generate compact views (modals)?', false)
        ->expectsQuestion('Want to generate files that assume you have Laravel Jetstream installed? If not, then an App Layout similar to Laravel Jetstream\'s will be created.', false)
        ->expectsQuestion('Want to create a Factory?', false)
        ->expectsQuestion('Want to create a Seeder?', false)
        ->expectsQuestion('What testing framework do you want to use?', TestingFrameworkEnum::PHPUNIT->value)
        ->assertExitCode(Command::FAILURE);

    // TODO
});
