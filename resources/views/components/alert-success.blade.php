<div {{ $attributes->merge([
    'class' => 'alert bg-green-100 border border-green-400 text-green-700 px-4 py-3 my-2 rounded relative'
]) }} role="alert">
    <strong class="font-bold">Success !</strong>
    <div class="alert-content">{{ $slot }}</div>
</div>
