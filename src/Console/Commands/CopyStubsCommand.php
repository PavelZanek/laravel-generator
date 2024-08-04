<?php

declare(strict_types=1);

namespace PavelZanek\LaravelGenerator\Console\Commands;

use Illuminate\Console\Command;

final class CopyStubsCommand extends Command
{
    protected $signature = 'laravel-generator:copy-stubs';
    protected $description = 'Copy stubs from the package to the application';

    public function handle(): void
    {
        $this->line('Copying stubs...');
        $this->copyDirectory(__DIR__.'/../../../stubs', resource_path('stubs/vendor/laravel-generator'));
        $this->info('Stubs copied successfully!');
    }

    /**
     * @param string $source
     * @param string $destination
     * @return void
     */
    private function copyDirectory(string $source, string $destination): void
    {
        if (!is_dir($source)) {
            $this->error("Source directory does not exist: {$source}");
            return;
        }

        if (!is_dir($destination)) {
            mkdir($destination, 0755, true);
        }

        foreach ($iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($source, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        ) as $item) {
            if ($item->isDir()) { // @phpstan-ignore-line
                mkdir($destination . DIRECTORY_SEPARATOR . $iterator->getSubPathName());
            } else {
                copy($item, $destination . DIRECTORY_SEPARATOR . $iterator->getSubPathName()); // @phpstan-ignore-line
            }
        }
    }
}
