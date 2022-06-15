@php
    $checked = $checked ?? 'false';
    $disabled = $disabled ?? 'false';

    $checkedAttributes = null;
    if ($checked === 'true') {
        $checkedAttributes = 'checked';
    }

    $disabledAttributes = null;
    if ($disabled === 'true') {
        $disabledAttributes = 'disabled';
    }
@endphp

<label for="{{ $id ?? '' }}">
    <input type="radio" name="{{ $name ?? '' }}" id="{{ $id ?? '' }}" value="{{ $value ?? '' }}" class="relative top-[0.10rem]" {{ $checkedAttributes }} {{ $disabledAttributes }}>
    <span class="ml-1">
       {{ $slot }}
    </span>
</label>