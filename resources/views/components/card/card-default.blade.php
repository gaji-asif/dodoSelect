<div {{ $attributes->merge([
    'class' => 'w-full bg-white rounded-md shadow py-5 mb-6'
]) }}>
    {{ $slot }}
</div>