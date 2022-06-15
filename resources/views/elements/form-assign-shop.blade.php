@csrf
<input type="hidden" name="id" value="{{ $staff_id }}">
<input type="hidden" name="assign_shops" value="true" />
@if($assigned_shops == null)
    @php($assigned_shops = [-1])
@endif
<div class="mt-4">
    @foreach($shops as $shop)
        @php($shop_type = json_decode($shop->shop_type))
        @if(count((array) $shop_type))
            @if(in_array($shop->id, $assigned_shops))
                <input type="checkbox" name="assigned_shops[]" value="{{ $shop->id }}" checked> {{ $shop->name }} <br />
            @else
                <input type="checkbox" name="assigned_shops[]" value="{{ $shop->id }}"> {{ $shop->name }} <br />
            @endif
        @else
            <input type="checkbox" disabled> {{ $shop->name }} <small style="color:red">(Set username & pass first)</small> <br />
        @endif
    @endforeach
</div>

<div class="flex justify-end py-6">
    <x-button type="reset" color="gray" class="mr-1" id="cancelModalAssignShop">
        {{ __('translation.Cancel') }}
    </x-button>
    <x-button type="submit" color="blue">
        {{ __('translation.Save') }}
    </x-button>
</div>
<script>
    $(document).ready(function() {
        $('#cancelModalAssignShop').click(function() {
            $('body').removeClass('modal-open');
            $('.modal-assign-shop').addClass('modal-hide');
        });
    });
</script>
