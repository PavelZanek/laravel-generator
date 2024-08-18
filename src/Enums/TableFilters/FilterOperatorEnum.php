<?php

declare(strict_types=1);

namespace PavelZanek\LaravelGenerator\Enums\TableFilters;

enum FilterOperatorEnum: string
{
    case EQUAL = '=';
    case LIKE = 'like';
    case GT = '>';
    case GTE = '>=';
    case LT = '<';
    case LTE = '<=';
}
