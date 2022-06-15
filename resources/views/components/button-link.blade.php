@switch($color)
    @case('red')
        <a {{ $attributes->merge([
            'href' => '#',
            'class' => 'h-10 relative top-[0.10rem] inline-flex items-center justify-center px-3 py-2 bg-red-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-600 active:bg-red-600 outline-none focus:outline-none focus:border-red-600 focus:ring ring-red-300 cursor-pointer transition ease-in-out duration-150'
            ]) }}>
            {{ $slot }}
        </a>
        @break
    @case('green')
        <a {{ $attributes->merge([
            'href' => '#',
            'class' => 'h-10 relative top-[0.10rem] inline-flex items-center justify-center px-3 py-2 bg-green-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-600 active:bg-green-600 outline-none focus:outline-none focus:border-green-600 focus:ring ring-green-300 cursor-pointer transition ease-in-out duration-150'
            ]) }}>
            {{ $slot }}
        </a>
        @break
    @case('blue')
        <a {{ $attributes->merge([
            'href' => '#',
            'class' => 'h-10 relative top-[0.10rem] inline-flex items-center justify-center px-3 py-2 bg-blue-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-600 active:bg-blue-600 outline-none focus:outline-none focus:border-blue-600 focus:ring ring-blue-300 cursor-pointer transition ease-in-out duration-150'
            ]) }}>
            {{ $slot }}
        </a>
        @break
    @case('yellow')
        <a {{ $attributes->merge([
            'href' => '#',
            'class' => 'h-10 relative top-[0.10rem] inline-flex items-center justify-center px-3 py-2 bg-yellow-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-yellow-600 active:bg-yellow-600 outline-none focus:outline-none focus:border-yellow-600 focus:ring ring-yellow-300 cursor-pointer transition ease-in-out duration-150'
            ]) }}>
            {{ $slot }}
        </a>
        @break
    @case('orange')
        <a {{ $attributes->merge([
            'href' => '#',
            'class' => 'h-10 relative top-[0.10rem] inline-flex items-center justify-center px-3 py-2 bg-orange-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-orange-600 active:bg-orange-600 outline-none focus:outline-none focus:border-orange-600 focus:ring ring-orange-300 cursor-pointer transition ease-in-out duration-150'
            ]) }}>
            {{ $slot }}
        </a>
        @break
    @case('gray')
        <a {{ $attributes->merge([
            'href' => '#',
            'class' => 'h-10 relative top-[0.10rem] inline-flex items-center justify-center px-3 py-2 bg-gray-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-600 active:bg-gray-600 outline-none focus:outline-none focus:border-gray-600 focus:ring ring-gray-300 cursor-pointer transition ease-in-out duration-150'
            ]) }}>
            {{ $slot }}
        </a>
        @break
    @default
        <a {{ $attributes->merge([
            'href' => '#',
            'class' => 'h-10 relative top-[0.10rem] inline-flex items-center justify-center px-3 py-2 bg-gray-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-600 active:bg-gray-600 outline-none focus:outline-none focus:border-gray-600 focus:ring ring-gray-300 cursor-pointer transition ease-in-out duration-150'
            ]) }}>
            {{ $slot }}
        </a>

@endswitch
