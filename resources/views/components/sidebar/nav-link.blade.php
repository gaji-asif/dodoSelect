@props(['active'])

@php
    $cssClass = $active ?? false
                ? 'w-full inline-flex items-center relative px-4 py-2 no-underline text-blue-600'
                : 'w-full inline-flex items-center relative px-4 py-2 no-underline text-gray-800'
@endphp

<a {{ $attributes->merge([
    'class' => $cssClass,
    'href' => '#'
]) }}>
    {{ $slot }}
</a>