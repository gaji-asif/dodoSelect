@props(['titleText'])

<div class="flex flex-row items-center justify-between mb-2">
    <h2 class="block whitespace-nowrap text-yellow-500 text-base font-bold">
        {{ $titleText }}
    </h2>
    <hr class="w-full ml-3 relative -top-1 border border-r-0 border-b-0 border-l-0 border-yellow-300">
    {{ $slot }}
</div>