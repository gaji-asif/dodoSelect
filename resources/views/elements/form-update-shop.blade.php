@csrf
<style type="text/css">
    .district_new, .sub_district_new, .province_new, .postcode_new{
        display: none;
    }
    .reset_nutton{
        border-radius: 5px;
    }
</style>

<input type="hidden" name="id" id="id" value="{{ $editData->id ?? '' }}">

<div class="form-group">
    <label for="name" class="font-weight-bold">
        Shop Name <x-form.required-mark/>
    </label>
    <input type="text" name='name' class="form-control" required value="{{ $editData->name ?? old('name') }}">
</div>
<div class="form-group">
    <label for="code" class="font-weight-bold">
        Shop Code <x-form.required-mark/>
    </label>
    <input type="text" name="code" class="form-control" required value="{{ $editData->code ?? old('code') }}">
</div>
<div class="form-group">
    <label for="address" class="font-weight-bold">Address</label>
    <input type="text" name='address' class="form-control" value="{{ $editData->address ?? old('address') }}">
</div>

<a style="float: right; cursor: pointer;" class="btn-action--red reset_nutton" onclick="reset_address();">Reset Address</a>
<br>

<div class="main_div">

    <div class="form-group">
        <label for="district" class="font-weight-bold">District</label>
        <div id="the-basicss">
            <input type="text" class="typeaheads district form-control" id="district" name="district" value="@if(isset($editData->district)){{$editData->district}} @endif">
            <input type="text" class="typeaheads district_new form-control" name="district_new" id="district_new">
        </div>
    </div>

    <div class="form-group">
        <label for="sub_district" class="font-weight-bold">Sub District</label>
        <div id="the-basicss1">
            <input type="text" class="typeaheads sub_district form-control" name="sub_district" id="sub_district" value="@if(isset($editData->sub_district)){{$editData->sub_district}} @endif">
            <input type="text" class="typeaheads sub_district_new form-control" name="sub_district_new" id="sub_district_new">
        </div>
    </div>

    <div class="form-group">
        <label for="province" class="font-weight-bold">Province</label>
        <div id="the-basicss2">
            <input type="text" class="typeaheads province form-control" name="province" id="province" value="@if(isset($editData->province)){{$editData->province}}@endif">
            <input type="text" class="typeaheads province_new form-control" name="province_new" id="province_new">
        </div>
    </div>

    <div class="form-group">
        <label for="postcode" class="font-weight-bold">Postcode</label>
        <div id="the-basicss3">
            <input type="text" class="typeaheads postcode form-control" name="postcode" id="postcode" value="@if(isset($editData->postcode)){{$editData->postcode}}@endif">
            <input type="text" class="typeaheads postcode_new form-control" name="postcode_new" id="postcode_new">
        </div>
    </div>

    <input type="hidden" id="full_address" name="full_address"
           value="@if(isset($editData->district)){{$editData->district.'/'.$editData->sub_district.'/'.$editData->province.'/'.$editData->postcode}}@endif">

</div>

@include('layouts.thailand_address')
{{--</div>--}}

<div class="form-group">
    <label for="phone" class="font-weight-bold">Phone Number</label>
    <input type="number" name='phone' class="form-control" value="{{ $editData->phone ?? old('phone') }}">
</div>
<div class="form-group">
    <label for="shopType" class="font-weight-bold">Shop Type</label><br />
    @php( $shop_type = json_decode($editData->shop_type ?? ''))
    <input id="shopeeCheckbox" type="checkbox" name='shopee' value="Shopee" @if(isset($shop_type->Shopee)) checked @endif /> Shopee <br />
    <div class="form-group shopeeCredentials" @if(isset($shop_type->Shopee)) style="display:block;" @else style="display:none;" @endif>
        <label for="username" class="font-weight-bold">Username & Password <small>(comma separated)</small></label>
        <input type="text" id="username" name='shopee_credentials' class="form-control" value="@if(isset($shop_type->Shopee)) {{ $shop_type->Shopee->username.','.$shop_type->Shopee->password }} @else {{ old('shopee_credentials') }} @endif" placeholder="e.g; user,pass">
    </div>
    <input id="lazadaCheckbox" type="checkbox" name='lazada' value="Lazada" @if(isset($shop_type->Lazada)) checked @endif /> Lazada
    <div class="form-group lazadaCredentials" @if(isset($shop_type->Lazada)) style="display:block;" @else style="display:none;" @endif>
        <label for="password" class="font-weight-bold">Username & Password <small>(comma separated)</small></label>
        <input type="text" id="password" name='lazada_credentials' class="form-control" value="@if(isset($shop_type->Lazada)) {{ $shop_type->Lazada->username.','.$shop_type->Lazada->password }} @else {{ old('lazada_credentials') }} @endif" placeholder="e.g; user,pass">
    </div>
</div>
<div class="form-group">
    <label for="logo" class="font-weight-bold">Upload Logo</label>
    <input type="file" onchange="previewFile(this);" class="form-control logo" name="logo" id="logo" style="height: auto">
</div>

@if(isset($editData->logo))
@if(Storage::disk('s3')->exists($editData->logo) && !empty($editData->logo))
    <img id="previewImg" class="mt-2 previewImg" width="130" height="130" src="{{Storage::disk('s3')->url($editData->logo)}}" alt="image">
@endif
@else
 <img id="previewImg" class="mt-2 previewImg" width="130" height="130" src="{{Storage::disk('s3')->url('uploads/No-Image-Found.png')}}" alt="image">
@endif


<div class="flex justify-end py-6">
    <x-button type="reset" color="gray" class="mr-1 cancelModalUpdate" id="cancelModalUpdate">
        {{ __('translation.Cancel') }}
    </x-button>
    <x-button type="submit" color="blue">
        {{ __('translation.Submit') }}
    </x-button>
</div>

<script>
    $(document).ready(function() {
        $('.cancelModalUpdate').click(function() {
            $('body').removeClass('modal-open');
            $('.modal-update').addClass('modal-hide');
        });

        $('#lazadaCheckbox').on("change",function () {
            if($(this).is(":checked"))
                $('.lazadaCredentials').show({
                    slideDown:150
                });
            else
                $('.lazadaCredentials').hide({
                    slideUp:150
                });
        });

        $('#shopeeCheckbox').on("change",function () {
            if($(this).is(":checked"))
                $('.shopeeCredentials').show({
                    slideDown:150
                });
            else
                $('.shopeeCredentials').hide({
                    slideUp:150
                });
        });
    });

    function previewFile(input){
        var file = $(".logo").get(0).files[0];

        if(file){
            var reader = new FileReader();
            reader.onload = function(){
                $(".previewImg").attr("src", reader.result);
            }
            reader.readAsDataURL(file);
        }
    }
</script>
