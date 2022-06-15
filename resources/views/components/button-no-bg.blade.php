@props(['color'])

@switch($color)
    @case('red')
        <button {{ $attributes->merge([
            'type' => 'button',
            'class' => 'h-10 relative top-[0.10rem] inline-flex items-center justify-center px-3 py-2 bg-transparent text-red-500 disabled:text-red-500 border border-transparent font-semibold text-xs text-white uppercase tracking-widest hover:text-red-600 active:text-red-600 outline-none focus:outline-none disabled:opacity-50 disabled:cursor-not-allowed transition ease-in-out duration-150'
            ]) }}>
            {{ $slot }}
        </button>
        @break
    @case('green')
        <button {{ $attributes->merge([
            'type' => 'button',
            'class' => 'h-10 relative top-[0.10rem] inline-flex items-center justify-center px-3 py-2 bg-transparent text-green-500 disabled:text-green-500 border border-transparent font-semibold text-xs text-white uppercase tracking-widest hover:text-green-600 active:text-green-600 outline-none focus:outline-none disabled:opacity-50 disabled:cursor-not-allowed transition ease-in-out duration-150'
            ]) }}>
            {{ $slot }}
        </button>
        @break
    @case('blue')
        <button {{ $attributes->merge([
            'type' => 'button',
            'class' => 'h-10 relative top-[0.10rem] inline-flex items-center justify-center px-3 py-2 bg-transparent text-blue-500 disabled:text-blue-500 border border-transparent font-semibold text-xs text-white uppercase tracking-widest hover:text-blue-600 active:text-blue-600 outline-none focus:outline-none disabled:opacity-50 disabled:cursor-not-allowed transition ease-in-out duration-150'
            ]) }}>
            {{ $slot }}
        </button>
        @break
    @case('yellow')
        <button {{ $attributes->merge([
            'type' => 'button',
            'class' => 'h-10 relative top-[0.10rem] inline-flex items-center justify-center px-3 py-2 bg-transparent text-yellow-500 disabled:text-yellow-500 border border-transparent font-semibold text-xs text-white uppercase tracking-widest hover:text-yellow-600 active:text-yellow-600 outline-none focus:outline-none disabled:opacity-50 disabled:cursor-not-allowed transition ease-in-out duration-150'
            ]) }}>
            {{ $slot }}
        </button>
        @break
    @case('orange')
        <button {{ $attributes->merge([
            'type' => 'button',
            'class' => 'h-10 relative top-[0.10rem] inline-flex items-center justify-center px-3 py-2 bg-transparent text-orange-500 disabled:text-orange-500 border border-transparent font-semibold text-xs text-white uppercase tracking-widest hover:text-orange-600 active:text-orange-600 outline-none focus:outline-none disabled:opacity-50 disabled:cursor-not-allowed transition ease-in-out duration-150'
            ]) }}>
            {{ $slot }}
        </button>
        @break
    @case('gray')
        <button {{ $attributes->merge([
            'type' => 'button',
            'class' => 'h-10 relative top-[0.10rem] inline-flex items-center justify-center px-3 py-2 bg-transparent text-gray-700 disabled:text-gray-700 border border-transparent font-semibold text-xs uppercase tracking-widest hover:text-gray-600 active:text-gray-600 outline-none focus:outline-none disabled:opacity-50 disabled:cursor-not-allowed transition ease-in-out duration-150'
            ]) }}>
            {{ $slot }}
        </button>
        @break
    @default
        <button {{ $attributes->merge([
            'type' => 'button',
            'class' => 'h-10 relative top-[0.10rem] inline-flex items-center justify-center px-3 py-2 bg-transparent text-gray-700 disabled:text-gray-700 border border-transparent font-semibold text-xs uppercase tracking-widest hover:text-gray-600 active:text-gray-600 outline-none focus:outline-none disabled:opacity-50 disabled:cursor-not-allowed transition ease-in-out duration-150'
            ]) }}>
            {{ $slot }}
        </button>

@endswitch
