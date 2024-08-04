<?php

declare(strict_types=1);

namespace PavelZanek\LaravelGenerator\Helpers;

use PavelZanek\LaravelGenerator\Enums\TableFilters\FilterInputTypeEnum;
use PavelZanek\LaravelGenerator\Enums\TableFilters\FilterOperatorEnum;
use PavelZanek\LaravelGenerator\Enums\TableFilters\FilterTypeEnum;
use Illuminate\Database\Eloquent\Model;

final readonly class TableColumn
{
    /**
     * @param string $name
     * @param bool $isSortable
     * @param bool $isFilterable
     * @param FilterOperatorEnum|null $filterOperator
     * @param FilterTypeEnum|null $filterType
     * @param FilterInputTypeEnum|null $filterInputType
     * @param array<array-key, array<string, mixed>>|null $filterSelectValues
     * @param mixed $value
     * @param mixed $wrapper
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
        if ($this->getValue() !== null && !is_callable($this->getValue())) {
            throw new \InvalidArgumentException("Value must be callable");
        }

        if ($this->getWrapper() !== null && !is_callable($this->getWrapper())) {
            throw new \InvalidArgumentException("Wrapper must be callable");
        }
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return bool
     */
    public function isSortable(): bool
    {
        return $this->isSortable;
    }

    /**
     * @return bool
     */
    public function isFilterable(): bool
    {
        return $this->isFilterable;
    }

    /**
     * @return FilterOperatorEnum|null
     */
    public function getFilterOperator(): ?FilterOperatorEnum
    {
        return $this->filterOperator;
    }

    /**
     * @return FilterTypeEnum|null
     */
    public function getFilterType(): ?FilterTypeEnum
    {
        return $this->filterType;
    }

    /**
     * @return FilterInputTypeEnum|null
     */
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

    /**
     * @return mixed
     */
    public function getValue(): mixed
    {
        return $this->value;
    }

    /**
     * @param \Illuminate\Database\Eloquent\Model $record
     * @return mixed|null
     */
    public function getTemplateValue(Model $record): mixed
    {
        if (is_callable($this->getValue())) {
            return call_user_func($this->getValue(), $record);
        }

        return null;
    }

    /**
     * @return mixed
     */
    public function getWrapper(): mixed
    {
        return $this->wrapper;
    }

    /**
     * @param \Illuminate\Database\Eloquent\Model $record
     * @param mixed $value
     * @return mixed
     */
    public function wrapValue(Model $record, mixed $value): mixed
    {
        if (is_callable($this->getWrapper())) {
            return call_user_func($this->getWrapper(), $record, $value);
        }
        return $record;
    }
}
