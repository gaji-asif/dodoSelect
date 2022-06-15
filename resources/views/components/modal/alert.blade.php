<div {{ $attributes->merge() }}>
    <div class="w-full fixed bg-black bg-opacity-70 inset-0 z-30 add_product_modal">
        <div class="w-full h-screen overflow-y-auto">
            <div class="bg-white w-11/12 sm:w-1/2 md:max-w-md lg:w-2/5 lg:max-w-lg xl:w-1/3 xl:max-w-md mx-auto rounded-lg text-left my-10">
                {{ $slot }}
            </div>
        </div>
    </div>
</div>