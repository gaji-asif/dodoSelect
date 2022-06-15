<div {{ $attributes->merge([
    'class' => 'alert bg-red-100 border border-red-400 text-red-700 px-4 my-2 py-3 rounded relative'
]) }} role="alert">
    <strong class="font-bold">Error !</strong>
    <div class="alert-content">{{ $slot }}</div>
</div>
