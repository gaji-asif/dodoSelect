@csrf

<div class="mt-6">
    <input type="hidden" name="id" value="{{ $data->id }}">

    <x-label>
        Order Status
    </x-label>
    <x-select id="status" name='status'>
        @foreach($statuses as $idx => $status)
            <option value="{{ $idx }}" {{ $data->status == $idx ? 'selected' : '' }}>{{ $status }}</option>
        @endforeach
    </x-select>
</div>

<div class="flex justify-center py-6">
    <x-button type="reset" color="gray" class="mr-1" id="btnCancelModalStatus">
        {{ __('translation.Cancel') }}
    </x-button>
    <x-button type="submit" color="blue" id="BtnSubmitChangeStatus" data-row_index="{{$row_index}}" data-id="{{$data->id}}" data-website_id="{{$data->website_id}}" data-order_id="{{$data->order_id}}">
        {{ __('translation.Update Status') }}
    </x-button>
</div>

<script>
    $(document).ready(function() {
        $('#btnCancelModalStatus').click(function() {
            $('.modal-status').doModal('close');
        });
    });
</script>

