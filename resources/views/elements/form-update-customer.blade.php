@csrf

<div>
    <input type="hidden" name="id" value="{{ $data->id }}">
    <div class="form-group font-weight-bold">
        <label for="customer_name">Customer Name</label>
        <input type="text" class="form-control" name="customer_name" id="customer_name" value="{{ $data->customer_name ? $data->customer_name : old('customer_name')}}" required autocomplete="off">
    </div>
    <div class="form-group font-weight-bold">
        <label for="contact_phone">Contact Phone</label>
        <input type="number" class="form-control" name="contact_phone" id="contact_phone" value="{{ $data->contact_phone ? $data->contact_phone : old('contact_phone') }}" required autocomplete="off">
    </div>
</div>

<div class="flex justify-end py-6">
    <x-button type="reset" color="gray" class="mr-2" id="cancelModalUpdate">
        {{ __('translation.Cancel') }}
    </x-button>
    <x-button type="submit" color="blue">
        {{ __('translation.Update') }}
    </x-button>
</div>

<script>
    $(document).on('click', '#cancelModalUpdate', function() {
        $('.modal-update').addClass('modal-hide');
    });
</script>
