<tbody>
    @foreach ($records as $record)
        <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
            @foreach ($tableColumns as $column => $columnData)
                @if($column === 'action')
                    <td class="px-6 py-4">
                        <div class="flex justify-start items-center gap-2">
                            <x-laravel-generator::links.link-navigate href="{{ route($route['edit'], $record->getId()) }}">
                                {{ $button['edit'] }}
                            </x-laravel-generator::links.link-navigate>
                            <x-laravel-generator::buttons.danger-button wire:click="confirmItemDeletion({{ $record->getId() }})" wire:loading.attr="disabled">
                                {{ $button['delete'] }}
                            </x-laravel-generator::buttons.danger-button>
                        </div>
                    </td>
                @else
                    <td class="px-6 py-4">
                        @if($columnData->getWrapper())
                            {!! $columnData->wrapValue($record, $columnData->getTemplateValue($record)) !!}
                        @else
                            {{ $columnData->getTemplateValue($record) }}
                        @endif
                    </td>
                @endif
            @endforeach
        </tr>
    @endforeach
</tbody>
