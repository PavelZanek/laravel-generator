<?php

declare(strict_types=1);

namespace PavelZanek\LaravelGenerator\Enums;

enum FrameworkEnum: string
{
    case LARAVEL = 'Laravel';
    case LIVEWIRE = 'Livewire';
}
