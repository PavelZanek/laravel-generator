<?php

declare(strict_types=1);

namespace PavelZanek\LaravelGenerator\Helpers;

use Illuminate\Database\Eloquent\Model;
use PavelZanek\LaravelGenerator\Enums\TableFilters\FilterInputTypeEnum;
use PavelZanek\LaravelGenerator\Enums\TableFilters\FilterOperatorEnum;
use PavelZanek\LaravelGenerator\Enums\TableFilters\FilterTypeEnum;

final readonly class TableColumn
{
    /**
     * @param  array<array-key, array<string, mixed>>|null  $filterSelectValues
     */
    public function __construct(
        public string $name,
        public bool $isSortable,
        public bool $isFilterable,
        public ?FilterOperatorEnum $filterOperator = null,
        public ?FilterTypeEnum $filterType = null,
        public ?FilterInputTypeEnum $filterInputType = null,
        public ?array $filterSelectValues = null,
        public mixed $value = null,
        public mixed $wrapper = null
    ) {
        if ($this->getValue() !== null && ! is_callable($this->getValue())) {
            throw new \InvalidArgumentException('Value must be callable');
        }

        if ($this->getWrapper() !== null && ! is_callable($this->getWrapper())) {
            throw new \InvalidArgumentException('Wrapper must be callable');
        }
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function isSortable(): bool
    {
        return $this->isSortable;
    }

    public function isFilterable(): bool
    {
        return $this->isFilterable;
    }

    public function getFilterOperator(): ?FilterOperatorEnum
    {
        return $this->filterOperator;
    }

    public function getFilterType(): ?FilterTypeEnum
    {
        return $this->filterType;
    }

    public function getFilterInputType(): ?FilterInputTypeEnum
    {
        return $this->filterInputType;
    }

    /**
     * @return array<array-key, mixed>|null
     */
    public function getFilterSelectValues(): ?array
    {
        return $this->filterSelectValues;
    }

    public function getValue(): mixed
    {
        return $this->value;
    }

    /**
     * @return mixed|null
     */
    public function getTemplateValue(Model $record): mixed
    {
        if (is_callable($this->getValue())) {
            return call_user_func($this->getValue(), $record);
        }

        return null;
    }

    public function getWrapper(): mixed
    {
        return $this->wrapper;
    }

    public function wrapValue(Model $record, mixed $value): mixed
    {
        if (is_callable($this->getWrapper())) {
            return call_user_func($this->getWrapper(), $record, $value);
        }

        return $record;
    }
}
