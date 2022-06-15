@props(['color'])

@switch($color)
    @case('red')
        <button {{ $attributes->merge([
            'type' => 'submit',
            'class' => 'h-7 relative top-[0.10rem] inline-flex items-center justify-center px-3 py-1 bg-red-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-600 active:bg-red-600 outline-none focus:outline-none focus:border-red-600 focus:ring ring-red-300 cursor-pointer disabled:opacity-25 transition ease-in-out duration-150'
            ]) }}>
            {{ $slot }}
        </button>
        @break
    @case('green')
        <button {{ $attributes->merge([
            'type' => 'submit',
            'class' => 'h-7 relative top-[0.10rem] inline-flex items-center justify-center px-3 py-1 bg-green-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-600 active:bg-green-600 outline-none focus:outline-none focus:border-green-600 focus:ring ring-green-300 cursor-pointer disabled:opacity-25 transition ease-in-out duration-150'
            ]) }}>
            {{ $slot }}
        </button>
        @break
    @case('blue')
        <button {{ $attributes->merge([
            'type' => 'submit',
            'class' => 'h-7 relative top-[0.10rem] inline-flex items-center justify-center px-3 py-1 bg-blue-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-600 active:bg-blue-600 outline-none focus:outline-none focus:border-blue-600 focus:ring ring-blue-300 cursor-pointer disabled:opacity-25 transition ease-in-out duration-150'
            ]) }}>
            {{ $slot }}
        </button>
        @break
    @case('yellow')
        <button {{ $attributes->merge([
            'type' => 'submit',
            'class' => 'h-7 relative top-[0.10rem] inline-flex items-center justify-center px-3 py-1 bg-yellow-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-yellow-600 active:bg-yellow-600 outline-none focus:outline-none focus:border-yellow-600 focus:ring ring-yellow-300 cursor-pointer disabled:opacity-25 transition ease-in-out duration-150'
            ]) }}>
            {{ $slot }}
        </button>
        @break
    @case('orange')
        <button {{ $attributes->merge([
            'type' => 'submit',
            'class' => 'h-7 relative top-[0.10rem] inline-flex items-center justify-center px-3 py-1 bg-orange-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-orange-600 active:bg-orange-600 outline-none focus:outline-none focus:border-orange-600 focus:ring ring-orange-300 cursor-pointer disabled:opacity-25 transition ease-in-out duration-150'
            ]) }}>
            {{ $slot }}
        </button>
        @break
    @case('gray')
        <button {{ $attributes->merge([
            'type' => 'submit',
            'class' => 'h-7 relative top-[0.10rem] inline-flex items-center justify-center px-3 py-1 bg-gray-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-600 active:bg-gray-600 outline-none focus:outline-none focus:border-gray-600 focus:ring ring-gray-300 cursor-pointer disabled:opacity-25 transition ease-in-out duration-150'
            ]) }}>
            {{ $slot }}
        </button>
        @break
    @default
        <button {{ $attributes->merge([
            'type' => 'submit',
            'class' => 'h-7 relative top-[0.10rem] inline-flex items-center justify-center px-3 py-1 bg-gray-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-600 active:bg-gray-600 outline-none focus:outline-none focus:border-gray-600 focus:ring ring-gray-300 cursor-pointer disabled:opacity-25 transition ease-in-out duration-150'
            ]) }}>
            {{ $slot }}
        </button>

@endswitch
