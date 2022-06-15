@props(['active'])

@php
    $cssClass = $active ?? false
                ? 'mb-1 rounded-md bg-blue-100 hover:bg-blue-200'
                : 'mb-1 rounded-md bg-transparent hover:bg-gray-100'
@endphp

<li {{ $attributes->merge([
    'class' => $cssClass
]) }}>
    {{ $slot }}
</li>
