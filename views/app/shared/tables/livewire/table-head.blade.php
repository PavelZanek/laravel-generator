<thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
    <tr>
        @foreach ($tableColumns as $column => $columnData)
            <th scope="col" class="px-6 py-3">
                @if($columnData->isSortable())
                    <a href="#" wire:click.prevent="sortBy('{{ $column }}')" class="flex items-center text-blue-600 dark:text-blue-400 hover:underline">
                        {{ $columnData->getName() }}
                        @if ($sortColumn === $column)
                            @if ($sortDirection === 'asc')
                                &uarr;
                        @else
                            &darr;
                        @endif
                        @endif
                    </a>
                @else
                    {{ $columnData->getName() }}
                @endif
            </th>
        @endforeach
    </tr>
    <tr>
        <form wire:submit.prevent>
            @foreach ($tableColumns as $column => $columnData)
                <th scope="col" class="px-6 py-3">
                    @if($column === 'action')
                        <div class="flex justify-start items-center gap-2">
                            @if(isset($button['filter']))
                                <x-laravel-generator::buttons.button type="submit">
                                    {{ $button['filter'] }}
                                </x-laravel-generator::buttons.button>
                            @endif
                            @if(! empty($this->filters))
                                <x-laravel-generator::buttons.danger-button wire:click="resetFilters" wire:loading.attr="disabled">
                                    {{ $button['reset_filter'] }}
                                </x-laravel-generator::buttons.danger-button>
                            @endif
                        </div>
                    @elseif($columnData->isFilterable())
                        @if($columnData->getFilterType()?->value === 'input')
                            @if($columnData->getFilterInputType()?->value === 'number' || $columnData->getFilterInputType()?->value === 'text')
                                <div class="flex items-center">
                                    <input type="{{ $columnData->getFilterInputType()?->value }}" wire:model.live.debounce.500ms="filters.{{ $column }}" id="filter-{{ $column }}" class="block w-full p-2 text-sm text-gray-600 border border-gray-300 rounded-lg bg-gray-50 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-gray-300 dark:focus:ring-blue-500 dark:focus:border-blue-500" />
                                </div>
                            @elseif($columnData->getFilterInputType()?->value === 'date')
                                <div class="flex items-center">
                                    <input type="date" wire:model.live.debounce.500ms="filters.{{ $column }}" id="filter-{{ $column }}" class="block w-full p-2 text-sm text-gray-600 border border-gray-300 rounded-lg bg-gray-50 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-gray-300 dark:focus:ring-blue-500 dark:focus:border-blue-500" />
                                </div>
                            @endif
                        @elseif($columnData->getFilterType()?->value === 'select')
                            <div class="flex items-center">
                                <select wire:model.live.debounce.500ms="filters.{{ $column }}" id="filter-{{ $column }}" class="block w-full p-2 text-sm text-gray-600 border border-gray-300 rounded-lg bg-gray-50 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-gray-300 dark:focus:ring-blue-500 dark:focus:border-blue-500">
                                    @foreach($columnData->getFilterSelectValues() as $value => $label)
                                        <option value="{{ $value }}">
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        @endif
                    @endif
                </th>
            @endforeach
        </form>
    </tr>
</thead>
