<ul {{ $attributes->merge([
    'class' => 'list-none px-0'
]) }}>
    {{ $slot }}
</ul>