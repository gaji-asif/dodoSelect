<div {{ $attributes->merge([
    'class' => 'alert bg-blue-100 border border-blue-300 text-blue-600 px-4 py-3 my-2 rounded relative'
]) }} role="alert">
    <strong class="font-bold">Info</strong>
    <div class="alert-content">{{ $slot }}</div>
</div>
