@props(['active'])

@php
    $cssClass = $active ?? false
                ? 'list-none px-0 pb-2 block'
                : 'list-none px-0 pb-2'
@endphp

<ul {{ $attributes->merge([
    'class' => $cssClass
]) }} style="display: none">
    {{ $slot }}
</ul>