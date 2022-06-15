@props(['active'])

@php
    $cssClass = $active ?? false
                ? 'block pl-11 pr-6 py-2 bg-transparent hover:bg-blue-200 text-blue-600 no-underline'
                : 'block pl-11 pr-6 py-2 bg-transparent hover:bg-blue-200 text-gray-800 no-underline'
@endphp

<a {{ $attributes->merge([
    'href' => '#',
    'class' => $cssClass
]) }}>
    {{ $slot }}
</a>