<?php

declare(strict_types=1);

use Illuminate\Console\Command;
use PavelZanek\LaravelGenerator\Console\Commands\GenerateGuestSectionCommand;

it(description: 'tests the GenerateGuestSection command', closure: function (): void {
    $this->artisan(GenerateGuestSectionCommand::class)
        ->expectsOutput('It works.')
        ->assertExitCode(Command::SUCCESS);
});
