<?php

declare(strict_types=1);

namespace PavelZanek\LaravelGenerator\Console\Commands;

use Illuminate\Console\Command;

final class GenerateGuestSectionCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'laravel-generator:guest';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate Guest files for given model (not yet available)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('It works.');

        return self::SUCCESS;
    }
}
