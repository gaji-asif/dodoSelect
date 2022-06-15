@props(['active'])

@php
    $cssClass = $active ?? false
                ? 'h-10 relative top-[0.10rem] inline-flex items-center justify-center mx-1 mb-2 px-3 py-2 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest bg-blue-500 hover:bg-blue-600 active:bg-blue-600 outline-none focus:outline-none focus:border-blue-600 focus:ring ring-blue-300 cursor-pointer disabled:opacity-25 transition ease-in-out duration-150'
                : 'h-10 relative top-[0.10rem] inline-flex items-center justify-center mx-1 mb-2 px-3 py-2 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest bg-gray-400 hover:bg-gray-500 active:bg-gray-600 outline-none focus:outline-none focus:border-gray-600 focus:ring ring-gray-300 cursor-pointer disabled:opacity-25 transition ease-in-out duration-150'
@endphp

<a {{ $attributes->merge([
    'href' => '#',
    'class' => $cssClass
    ]) }}>
    {{ $slot }}
</a>