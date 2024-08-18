<tbody>
    @foreach ($records as $record)
        <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
            @foreach ($tableColumns as $column => $columnData)
                @if($column === 'action')
                    <td class="px-6 py-4">
                        <div class="flex justify-start items-center gap-2">
                            <x-laravel-generator::links.secondary-link href="{{ route($route['edit'], $record->getId()) }}">
                                {{ $button['edit'] }}
                            </x-laravel-generator::links.secondary-link>
                            <form action="{{ route($route['delete'], $record->getId()) }}" method="POST" onsubmit="return confirm('{{ $button['confirm_delete'] }}');">
                                <input type="hidden" name="_method" value="DELETE">
                                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                <x-laravel-generator::buttons.danger-button type="submit">
                                    {{ $button['delete'] }}
                                </x-laravel-generator::buttons.danger-button>
                            </form>
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
