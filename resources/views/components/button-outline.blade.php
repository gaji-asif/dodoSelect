@props(['color'])

@switch($color)
    @case('red')
        <button {{ $attributes->merge([
            'type' => 'button',
            'class' => 'h-10 relative top-[0.10rem] inline-flex items-center justify-center px-3 py-2 bg-transparent text-red-500 disabled:text-red-500 border border-solid border-red-400 rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:text-red-600 active:text-red-600 outline-none focus:outline-none focus:border-red-600 focus:ring ring-red-300 disabled:opacity-50 disabled:cursor-not-allowed transition ease-in-out duration-150'
            ]) }}>
            {{ $slot }}
        </button>
        @break
    @case('green')
        <button {{ $attributes->merge([
            'type' => 'button',
            'class' => 'h-10 relative top-[0.10rem] inline-flex items-center justify-center px-3 py-2 bg-transparent text-green-500 disabled:text-green-500 border border-solid border-green-400 rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:text-green-600 active:text-green-600 outline-none focus:outline-none focus:border-green-600 focus:ring ring-green-300 disabled:opacity-50 disabled:cursor-not-allowed transition ease-in-out duration-150'
            ]) }}>
            {{ $slot }}
        </button>
        @break
    @case('blue')
        <button {{ $attributes->merge([
            'type' => 'button',
            'class' => 'h-10 relative top-[0.10rem] inline-flex items-center justify-center px-3 py-2 bg-transparent text-blue-500 disabled:text-blue-500 border border-solid border-blue-400 rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:text-blue-600 active:text-blue-600 outline-none focus:outline-none focus:border-blue-600 focus:ring ring-blue-300 disabled:opacity-50 disabled:cursor-not-allowed transition ease-in-out duration-150'
            ]) }}>
            {{ $slot }}
        </button>
        @break
    @case('yellow')
        <button {{ $attributes->merge([
            'type' => 'button',
            'class' => 'h-10 relative top-[0.10rem] inline-flex items-center justify-center px-3 py-2 bg-transparent text-yellow-500 disabled:text-yellow-500 border border-solid border-yellow-400 rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:text-yellow-600 active:text-yellow-600 outline-none focus:outline-none focus:border-yellow-600 focus:ring ring-yellow-300 disabled:opacity-50 disabled:cursor-not-allowed transition ease-in-out duration-150'
            ]) }}>
            {{ $slot }}
        </button>
        @break
    @case('orange')
        <button {{ $attributes->merge([
            'type' => 'button',
            'class' => 'h-10 relative top-[0.10rem] inline-flex items-center justify-center px-3 py-2 bg-transparent text-orange-500 disabled:text-orange-500 border border-solid border-orange-400 rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:text-orange-600 active:text-orange-600 outline-none focus:outline-none focus:border-orange-600 focus:ring ring-orange-300 disabled:opacity-50 disabled:cursor-not-allowed transition ease-in-out duration-150'
            ]) }}>
            {{ $slot }}
        </button>
        @break
    @case('gray')
        <button {{ $attributes->merge([
            'type' => 'button',
            'class' => 'h-10 relative top-[0.10rem] inline-flex items-center justify-center px-3 py-2 bg-transparent text-gray-500 disabled:text-gray-500 border border-solid border-gray-400 rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:text-gray-600 active:text-gray-600 outline-none focus:outline-none focus:border-gray-600 focus:ring ring-gray-300 disabled:opacity-50 disabled:cursor-not-allowed transition ease-in-out duration-150'
            ]) }}>
            {{ $slot }}
        </button>
        @break
    @default
        <button {{ $attributes->merge([
            'type' => 'button',
            'class' => 'h-10 relative top-[0.10rem] inline-flex items-center justify-center px-3 py-2 bg-transparent text-gray-500 disabled:text-gray-500 border border-solid border-gray-400 rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:text-gray-600 active:text-gray-600 outline-none focus:outline-none focus:border-gray-600 focus:ring ring-gray-300 disabled:opacity-50 disabled:cursor-not-allowed transition ease-in-out duration-150'
            ]) }}>
            {{ $slot }}
        </button>

@endswitch
