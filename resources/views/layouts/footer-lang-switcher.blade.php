<div class="flex flex-row items-center justify-center gap-2">
    <label for="__lang_switcher" class="relative top-[0.10rem] text-gray-600">
        {{ __('translation.language') }} :
    </label>
    <select id="__lang_switcher" class="w-32 px-2 py-1 rounded-md border-transparent outline-none focus:outline-none bg-white">
        @foreach ($userPrefLangs as $value => $label)
            <option value="{{ $value }}" @if ($value == app()->getLocale()) selected @endif>
                {{ $label }}
            </option>
        @endforeach
    </select>
</div>

@push('bottom_js')
    <script src="{{ asset('js/lang-switcher.js?_=' . rand()) }}"></script>
@endpush