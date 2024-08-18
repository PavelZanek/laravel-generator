<thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
    <tr>
        @foreach ($tableColumns as $column => $columnData)
            <th scope="col" class="px-6 py-3">
                @if($columnData->isSortable())
                    <a href="{{ route($route, \array_merge(request()->all(), ['sort' => $column, 'direction' => $sortColumn === $column && $sortDirection === 'asc' ? 'desc' : 'asc'])) }}" class="flex items-center text-blue-600 dark:text-blue-400 hover:underline">
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
        <form method="GET" action="{{ route($route) }}">
            <input type="hidden" name="sort" value="{{ $sortColumn }}">
            <input type="hidden" name="direction" value="{{ $sortDirection }}">

            @foreach ($tableColumns as $column => $columnData)
                <th scope="col" class="px-6 py-3">
                    @if($column === 'action')
                        <div class="flex justify-start items-center gap-2">
                            <x-laravel-generator::buttons.button type="submit">
                                {{ $button['filter'] }}
                            </x-laravel-generator::buttons.button>
                            @if(! empty($filters))
                                <x-laravel-generator::links.danger-link href="{{ route($route) }}">
                                    {{ $button['reset_filter'] }}
                                </x-laravel-generator::links.danger-link>
                            @endif
                        </div>
                    @elseif($columnData->isFilterable())
                        @if($columnData->getFilterType()?->value === 'input')
                            @if($columnData->getFilterInputType()?->value === 'number' || $columnData->getFilterInputType()?->value === 'text')
                                <div class="flex items-center">
                                    <input type="{{ $columnData->getFilterInputType()?->value }}" name="filters[{{ $column }}]" id="filter-{{ $column }}" value="{{ $filters[$column] ?? '' }}" class="block w-full p-2 text-sm text-gray-600 border border-gray-300 rounded-lg bg-gray-50 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-gray-300 dark:focus:ring-blue-500 dark:focus:border-blue-500" />
                                </div>
                            @elseif($columnData->getFilterInputType()?->value === 'date')
                                <div class="flex items-center">
                                    <input type="date" name="filters[{{ $column }}]" id="filter-{{ $column }}" value="{{ $filters[$column] ?? '' }}" class="block w-full p-2 text-sm text-gray-600 border border-gray-300 rounded-lg bg-gray-50 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-gray-300 dark:focus:ring-blue-500 dark:focus:border-blue-500" />
                                </div>
                            @endif
                        @elseif($columnData->getFilterType()?->value === 'select')
                            <div class="flex items-center">
                                <select name="filters[{{ $column }}]" id="filter-{{ $column }}" class="block w-full p-2 text-sm text-gray-600 border border-gray-300 rounded-lg bg-gray-50 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-gray-300 dark:focus:ring-blue-500 dark:focus:border-blue-500">
                                    @foreach($columnData->getFilterSelectValues() as $value => $label)
                                        <option value="{{ $value }}" {{ isset($filters[$column]) && $filters[$column] == $value ? 'selected' : '' }}>
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
