@props(['color'])

@switch($color)
    @case('red')
        <button {{ $attributes->merge([
            'type' => 'submit',
            'class' => 'h-[2.5rem] relative top-[0.10rem] inline-flex items-center justify-center px-3 py-2 whitespace-nowrap bg-red-500 disabled:bg-red-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-600 active:bg-red-600 outline-none focus:outline-none focus:border-red-600 focus:ring ring-red-300 cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed transition ease-in-out duration-150'
            ]) }}>
            {{ $slot }}
        </button>
        @break
    @case('red-text')
        <button {{ $attributes->merge([
            'type' => 'submit',
            'class' => 'h-[2.5rem] relative top-[0.10rem] inline-flex items-center justify-center px-3 py-2 whitespace-nowrap bg-transparent border border-transparent rounded-md font-semibold text-xs text-red-500 uppercase tracking-widest outline-none focus:outline-none cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed transition ease-in-out duration-150'
            ]) }}>
            {{ $slot }}
        </button>
        @break
    @case('green')
        <button {{ $attributes->merge([
            'type' => 'submit',
            'class' => 'h-[2.5rem] relative top-[0.10rem] inline-flex items-center justify-center px-3 py-2 whitespace-nowrap bg-green-500 disabled:bg-green-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-600 active:bg-green-600 outline-none focus:outline-none focus:border-green-600 focus:ring ring-green-300 cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed transition ease-in-out duration-150'
            ]) }}>
            {{ $slot }}
        </button>
        @break
    @case('blue')
        <button {{ $attributes->merge([
            'type' => 'submit',
            'class' => 'h-[2.5rem] relative top-[0.10rem] inline-flex items-center justify-center px-3 py-2 whitespace-nowrap bg-blue-500 disabled:bg-blue-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-600 active:bg-blue-600 outline-none focus:outline-none focus:border-blue-600 focus:ring ring-blue-300 cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed transition ease-in-out duration-150'
            ]) }}>
            {{ $slot }}
        </button>
        @break
    @case('yellow')
        <button {{ $attributes->merge([
            'type' => 'submit',
            'class' => 'h-[2.5rem] relative top-[0.10rem] inline-flex items-center justify-center px-3 py-2 whitespace-nowrap bg-yellow-500 disabled:bg-yellow-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-yellow-600 active:bg-yellow-600 outline-none focus:outline-none focus:border-yellow-600 focus:ring ring-yellow-300 cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed transition ease-in-out duration-150'
            ]) }}>
            {{ $slot }}
        </button>
        @break
    @case('orange')
        <button {{ $attributes->merge([
            'type' => 'submit',
            'class' => 'h-[2.5rem] relative top-[0.10rem] inline-flex items-center justify-center px-3 py-2 whitespace-nowrap bg-orange-500 disabled:bg-orange-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-orange-600 active:bg-orange-600 outline-none focus:outline-none focus:border-orange-600 focus:ring ring-orange-300 cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed transition ease-in-out duration-150'
            ]) }}>
            {{ $slot }}
        </button>
        @break
    @case('gray')
        <button {{ $attributes->merge([
            'type' => 'submit',
            'class' => 'h-[2.5rem] relative top-[0.10rem] inline-flex items-center justify-center px-3 py-2 whitespace-nowrap bg-gray-500 disabled:bg-gray-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-600 active:bg-gray-600 outline-none focus:outline-none focus:border-gray-600 focus:ring ring-gray-300 cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed transition ease-in-out duration-150'
            ]) }}>
            {{ $slot }}
        </button>
        @break
    @default
        <button {{ $attributes->merge([
            'type' => 'submit',
            'class' => 'h-[2.5rem] relative top-[0.10rem] inline-flex items-center justify-center px-3 py-2 whitespace-nowrap bg-gray-500 disabled:bg-gray-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-600 active:bg-gray-600 outline-none focus:outline-none focus:border-gray-600 focus:ring ring-gray-300 cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed transition ease-in-out duration-150'
            ]) }}>
            {{ $slot }}
        </button>

@endswitch
