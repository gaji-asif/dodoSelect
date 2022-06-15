@props(['active'])

@php
$classes = $active ?? false
            ? 'w-full cursor-pointer inline-flex items-center px-4 py-2 text-sm font-medium leading-5 text-gray-900 focus:outline-none transition duration-150 ease-in-out'
            : 'w-full cursor-pointer inline-flex items-center px-4 py-2 text-sm font-medium leading-5 text-gray-500 hover:text-gray-focus:outline-none focus:text-gray-700 transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
