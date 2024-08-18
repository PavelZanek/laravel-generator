@props(['options' => [], 'selected' => null])

<select {!! $attributes->merge(['class' => 'bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500']) !!}>
    @foreach ($options as $option)
        <option @if($option !== '') value="{{ $option['value'] }}" @endif {{ $selected === $option['value'] ? 'selected' : '' }}>
            {{ $option['name'] }}
        </option>
    @endforeach
</select>
