<a {{ $attributes->merge([
    'href' => '#',
    'class' => 'text-blue-600 underline hover:underline'
]) }}>
    {{ $slot }}
</a>