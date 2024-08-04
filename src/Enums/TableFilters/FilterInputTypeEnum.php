<?php

declare(strict_types=1);

namespace PavelZanek\LaravelGenerator\Enums\TableFilters;

enum FilterInputTypeEnum: string
{
    case TEXT = 'text';
    case NUMBER = 'number';
    case DATE = 'date';
}
