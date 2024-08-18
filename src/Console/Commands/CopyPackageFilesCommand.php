<?php

declare(strict_types=1);

namespace PavelZanek\LaravelGenerator\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

final class CopyPackageFilesCommand extends Command
{
    protected $signature = 'laravel-generator:copy-package-files';
    protected $description = 'Copy files from the package to the application';

    /**
     * @return int
     */
    public function handle(): int
    {
        /** @var array<int, string> $files */
        $files = $this->choice(
            question: 'What files do you want to copy to your application?',
            choices: [
                'Stubs',
                'FilterInputTypeEnum.php',
                'FilterOperatorEnum.php',
                'FilterTypeEnum.php',
                'GenerateAppSectionCommand.php',
                'GenerateGuestSectionCommand.php',
                'TableColumn.php',
                'FilterService.php',
            ],
            multiple: true
        );

        foreach ($files as $file) {
            if ($file === 'Stubs') {
                $this->line('Copying stubs...');
                $this->copyDirectory(
                    (string)realpath(__DIR__.'/../../../stubs'),
                    resource_path('stubs/vendor/laravel-generator')
                );
                $this->info('Stubs copied successfully!');
                continue;
            }

            $data = match ($file) { // @phpstan-ignore-line
                'GenerateAppSectionCommand.php', 'GenerateGuestSectionCommand.php' => [
                    'source' => realpath(__DIR__.'/'.$file),
                    'destination' => app_path('Console/Commands/'.$file),
                ],
                'FilterInputTypeEnum.php', 'FilterOperatorEnum.php', 'FilterTypeEnum.php' => [
                    'source' => realpath(__DIR__.'/../../Enums/TableFilters/'.$file),
                    'destination' => app_path('Enums/TableFilters/'.$file),
                ],
                'TableColumn.php' => [
                    'source' => realpath(__DIR__.'/../../Helpers/'.$file),
                    'destination' => app_path('Helpers/'.$file),
                ],
                'FilterService.php' => [
                    'source' => realpath(__DIR__.'/../../Services/'.$file),
                    'destination' => app_path('Services/'.$file),
                ],
            };

            $this->copyFile(source: (string)$data['source'], destination: $data['destination']);
        }

        return self::SUCCESS;
    }

    /**
     * @param string $source
     * @param string $destination
     * @return void
     */
    private function copyDirectory(string $source, string $destination): void
    {
        if (! is_dir($source)) {
            $this->error("Source directory does not exist: {$source}");

            return;
        }

        if (! is_dir($destination)) {
            mkdir($destination, 0755, true);
        }

        foreach ($iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($source, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        ) as $item) {
            $destPath = $destination.DIRECTORY_SEPARATOR.$iterator->getSubPathName();
            if ($item->isDir()) { // @phpstan-ignore-line
                if (! is_dir($destPath)) {
                    mkdir($destPath, 0755, true);
                }
            } else {
                copy($item->getRealPath(), $destPath); // @phpstan-ignore-line
            }
        }
    }

    /**
     * @param string $source
     * @param string $destination
     * @return void
     */
    private function copyFile(string $source, string $destination): void
    {
        if (! File::exists($destination)) {
            if (! File::exists(dirname($destination))) {
                File::makeDirectory(dirname($destination), 0755, true);
            }
            File::copy($source, $destination);
            $this->info('Copied '.$source.' to '.$destination.'.');
        } else {
            $this->warn($source.' already exists in '.$destination.', skipping.');
        }
    }
}
