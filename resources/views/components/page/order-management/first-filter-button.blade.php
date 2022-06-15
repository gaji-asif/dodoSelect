@props(['active'])

@php
    $class = $active ?? false
            ? 'h-24 flex flex-col items-center justify-center outline-none focus:outline-none border border-solid border-blue-500 hover:border-blue-500 rounded-md bg-transparent text-blue-500 hover:text-blue-500 transition delay-150'
            : 'h-24 flex flex-col items-center justify-center outline-none focus:outline-none border border-solid border-gray-500 hover:border-blue-500 rounded-md bg-transparent text-gray-900 hover:text-blue-500 transition delay-150';
@endphp

<button type="button" {{ $attributes->merge([
    'class' => $class
]) }}>
    {{ $slot }}
</button>