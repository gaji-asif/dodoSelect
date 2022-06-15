<div {{ $attributes->merge([
    'class' => 'w-full'
]) }}>
    <h1 class="text-xl text-gray-800 font-bold leading-tight relative top-1">
        {{ $slot }}
    </h1>
</div>