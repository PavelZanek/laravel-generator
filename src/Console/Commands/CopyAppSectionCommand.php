<?php

declare(strict_types=1);

namespace PavelZanek\LaravelGenerator\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

final class CopyAppSectionCommand extends Command
{
    protected $signature = 'laravel-generator:copy-app-section-command';
    protected $description = 'Copy GenerateAppSectionCommand from the package to the application';

    /**
     * @return void
     */
    public function handle(): void
    {
        $this->line('Copying AppSectionCommand...');
        $this->copyCommand();
        $this->info('AppSectionCommand copied successfully!');
        $this->warn('Don\'t forget to edit the namespace.');
    }

    /**
     * @return void
     */
    private function copyCommand(): void
    {
        $commandSourcePath = __DIR__ . '/GenerateAppSectionCommand.php';
        $commandDestinationPath = app_path('Console/Commands/GenerateAppSectionCommand.php');

        if (!File::exists($commandDestinationPath)) {
            if (!File::exists(dirname($commandDestinationPath))) {
                File::makeDirectory(dirname($commandDestinationPath), 0755, true);
            }
            File::copy($commandSourcePath, $commandDestinationPath);
            $this->info('Copied GenerateAppSectionCommand to app/Console/Commands');
        } else {
            $this->warn('GenerateAppSectionCommand already exists in app/Console/Commands, skipping.');
        }
    }
}
