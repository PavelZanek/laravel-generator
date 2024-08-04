<?php

declare(strict_types=1);

namespace PavelZanek\LaravelGenerator\Enums\TableFilters;

enum FilterTypeEnum: string
{
    case INPUT = 'input';
    case SELECT = 'select';
}
