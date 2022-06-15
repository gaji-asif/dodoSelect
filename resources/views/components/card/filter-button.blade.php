@props(['dataStatus', 'label'])

<button {{ $attributes->merge([
    'class' => 'card-filter-button'
]) }}
    data-status="{{ $dataStatus ?? '' }}">
    <span class="card-filter-button--label">
        {{ $label ?? '' }}
    </span>
    <span class="card-filter-button--total">
        ({{ $slot }})
    </span>
</button>