<?php

declare(strict_types=1);

namespace PavelZanek\LaravelGenerator\Enums;

enum TestingFrameworkEnum: string
{
    case PHPUNIT = 'PHPUnit';
    case PEST = 'Pest';
}
