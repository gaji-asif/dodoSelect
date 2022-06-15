<button {{ $attributes->merge([
        'class' => 'order-purchase__filter'
    ]) }}
    data-status="{{ $dataStatus ?? '' }}">
    <span class="order-purchase__filter-label">
        {{ $label ?? '' }}
    </span>
    <span class="order-purchase__filter-total">
        ({{ $slot }})
    </span>
</button>