<?php

declare(strict_types=1);

namespace PavelZanek\LaravelGenerator\Enums\TableFilters;

enum FilterOperatorEnum: string
{
    case EQUAL = 'equal';
    case LIKE = 'like';
}
