<?php

namespace PavelZanek\LaravelGenerator\Services;

use Illuminate\Database\Eloquent\Builder;
use PavelZanek\LaravelGenerator\Enums\TableFilters\FilterOperatorEnum;
use PavelZanek\LaravelGenerator\Enums\TableFilters\FilterTypeEnum;
use PavelZanek\LaravelGenerator\Helpers\TableColumn;

class FilterService
{
    /**
     * Apply filters to the query.
     *
     * @param Builder $query
     * @param array<array-key, mixed> $filters
     * @param array<array-key, mixed> $tableColumns
     * @return Builder
     */
    public function applyFilters(Builder $query, array $filters, array $tableColumns): Builder
    {
        foreach ($filters as $column => $value) {
            if ($this->shouldApplyFilter($value)) {
                $tableColumn = $tableColumns[$column];
                $operator = $tableColumn->getFilterOperator()?->value ?? '=';

                if ($this->isSelectFilterWithValues($tableColumn)) {
                    $value = $this->normalizeSelectValue($value);
                }

                if ($value !== null) {
                    if ($this->isRelationshipColumn($column)) {
                        $this->applyRelationshipFilter($query, $column, $operator, $value);
                    } else {
                        $this->applyBasicFilter($query, $column, $operator, $value);
                    }
                }
            }
        }

        return $query;
    }

    /**
     * Check if the filter value should be applied.
     *
     * @param mixed $value
     * @return bool
     */
    protected function shouldApplyFilter(mixed $value): bool
    {
        return $value !== null && $value !== '';
    }

    /**
     * Check if the column is a select filter with predefined values.
     *
     * @param \PavelZanek\LaravelGenerator\Helpers\TableColumn $tableColumn
     * @return bool
     */
    protected function isSelectFilterWithValues(TableColumn $tableColumn): bool
    {
        return $tableColumn->getFilterType()?->value === FilterTypeEnum::SELECT->value
            && $tableColumn->getFilterSelectValues();
    }

    /**
     * Normalize the value for select filters.
     *
     * @param mixed $value
     * @return mixed
     */
    protected function normalizeSelectValue(mixed $value): mixed
    {
        return $value === 'not_set' ? null : $value;
    }

    /**
     * Check if the column is a relationship column.
     *
     * @param string $column
     * @return bool
     */
    protected function isRelationshipColumn(string $column): bool
    {
        return \strpos($column, ':') !== false;
    }

    /**
     * Apply a basic filter to the query.
     *
     * @param Builder $query
     * @param string $column
     * @param string $operator
     * @param mixed $value
     * @return void
     */
    protected function applyBasicFilter(Builder $query, string $column, string $operator, mixed $value): void
    {
        $query->where($column, $operator, $operator === FilterOperatorEnum::LIKE->value ? "%{$value}%" : $value);
    }

    /**
     * Apply filters to the query for related models.
     *
     * @param Builder $query
     * @param string $column
     * @param string $operator
     * @param mixed $value
     * @return void
     */
    protected function applyRelationshipFilter(Builder $query, string $column, string $operator, mixed $value): void
    {
        $relationParts = preg_split('/[.:]/', $column);
        $relation = array_shift($relationParts);
        $column = implode('.', $relationParts);

        $query->whereHas($relation, function (Builder $query) use ($column, $operator, $value) {
            $this->applyBasicFilter($query, $column, $operator, $value);
        });
    }
}
