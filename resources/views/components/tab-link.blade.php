@props(['active'])

@php
    $cssClass = $active ?? false
                ? 'h-9 px-4 py-2 mr-2 border border-solid border-blue-500 rounded-md bg-blue-500 hover:bg-blue-500 text-white hover:text-white transition-all duration-300 whitespace-nowrap'
                : 'h-9 px-4 py-2 mr-2 border border-solid border-blue-500 rounded-md bg-white hover:bg-blue-500 text-blue-500 hover:text-white transition-all duration-300 whitespace-nowrap'

@endphp

<a {{ $attributes->merge([
    'href' => '#',
    'class' => $cssClass
]) }}>
    {{ $slot }}
</a>