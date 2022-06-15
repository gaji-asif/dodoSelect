@props(['title' => '', 'sm' => 12, 'md' => 12, 'class' => ''])

<div class="col-span-{{ $sm }} md:col-span-{{ $md }} {{ $class }}">
    <div class="bg-white rounded-md w-full shadow pb-2">
        <div class="p-6">
            <div class="md:flex md:justify-between md:items-center">
                <div>
                    <h1 class="text-xl text-gray-800 font-bold leading-tight">{{ $title }}</h1>
                </div>
            </div>
            <div class="mt-4 mb-8 relative">
                {{ $slot }}
            </div>
        </div>
    </div>
</div>
