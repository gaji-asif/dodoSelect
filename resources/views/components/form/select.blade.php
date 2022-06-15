<select {{ $attributes->merge([
    'class' => 'block w-full h-10 px-2 py-2 rounded-md shadow-sm border border-solid border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 mt-1 bg-white disabled:bg-gray-100'])
}}>
    {{ $slot }}
</select>
