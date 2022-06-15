<div {{ $attributes->merge([
    'class' => 'w-full fixed bg-black bg-opacity-70 inset-0 z-30 transition duration-300 modal-hide'
]) }}>
    <div class="modal-overflow w-full h-screen overflow-y-auto">
        <div class="bg-white w-11/12 sm:w-1/2 md:w-11/12 lg:w-3/4 lg:max-w-4xl mx-auto rounded-lg text-left my-10">
            {{ $slot }}
        </div>
    </div>
</div>