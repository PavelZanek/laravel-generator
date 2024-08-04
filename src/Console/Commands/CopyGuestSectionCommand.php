<?php

namespace PavelZanek\LaravelGenerator\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

final class CopyGuestSectionCommand extends Command
{
    protected $signature = 'laravel-generator:copy-app-section-command';
    protected $description = 'Copy GenerateGuestSectionCommand from the package to the application';

    /**
     * @return void
     */
    public function handle(): void
    {
        $this->line('Copying GenerateGuestSectionCommand...');
        $this->copyCommand();
        $this->info('GenerateGuestSectionCommand copied successfully!');
        $this->warn('Don\'t forget to edit the namespace.');
    }

    /**
     * @return void
     */
    private function copyCommand(): void
    {
        $commandSourcePath = __DIR__ . '/GenerateGuestSectionCommand.php';
        $commandDestinationPath = app_path('Console/Commands/GenerateGuestSectionCommand.php');

        if (!File::exists($commandDestinationPath)) {
            if (!File::exists(dirname($commandDestinationPath))) {
                File::makeDirectory(dirname($commandDestinationPath), 0755, true);
            }
            File::copy($commandSourcePath, $commandDestinationPath);
            $this->info('Copied GenerateGuestSectionCommand to app/Console/Commands');
        } else {
            $this->warn('GenerateGuestSectionCommand already exists in app/Console/Commands, skipping.');
        }
    }
}
