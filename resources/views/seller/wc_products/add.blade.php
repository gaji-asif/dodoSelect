<x-app-layout>

    @section('title')
    {{ __('translation.Create Order') }}
    @endsection

    @push('top_css')
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css" />
    <link rel="stylesheet" href="{{ asset('css/typeaheadjs.css') }}">
    <link rel="stylesheet" href="{{ asset('css/custom_css.css') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.19/dist/sweetalert2.min.css">
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote.min.css" rel="stylesheet">
    @endpush

    @if (in_array('Can access menu: Order Management', session('assignedPermissions')))

    <div class="col-span-12">
        <x-card.card-default>
        <x-card.header>
        <x-card.back-button href="{{ route('wc_products.index') }}" />
        <x-card.title>
        Create Product

    </x-card.title>

</x-card.header>
<x-card.body>

<x-alert-danger class="mb-6 alert hidden" id="__alertDanger">
    <div id="__alertDangerContent"></div>
</x-alert-danger>

<x-section.title>
Product Details
</x-section.title>

<form action="#" method="post" id="__formEditWooProduct" enctype="multipart/form-data">
    @csrf
    <div class="row mb-3">
        <div class="col-lg-6">
           <div>
            <x-label for="contact_name">
                {{ __('translation.Shop Name') }}<x-form.required-mark/>
            </x-label>
            <select class="form-control" id="shop_id" name="shop_id">
                <option value="">Select Shop</option>
                @if($shops)
                @foreach($shops as $shop)
                <option value="{{$shop->id}}">{{$shop->name}}</option>
                @endforeach
                @endif
                
            </select>
        </div>
    </div>
    
</div>    
<div class="row mb-3">
    <div class="col-lg-6">
       <div>
        <x-label for="contact_name">
            {{ ucwords(__('translation.product type')) }} <x-form.required-mark/>
        </x-label>
        <select class="form-control" id="product_type" name="product_type">
            <option value="">Select type</option>
            <option value="simple">Simple</option>
            <option value="variable">Variable</option>
        </select>
    </div>
</div>
<div class="col-lg-6">
   <div>
    <x-label for="contact_name">
        {{ __('translation.Status') }}<x-form.required-mark/>
    </x-label>
    <select class="form-control" id="status" name="status">
        <option value="">Select Status</option>
        @if($product_statuss)
        @foreach($product_statuss as $value)
        <option value="{{$value}}">{{$value}}</option>
        @endforeach
        @endif

    </select>
</div>
</div>
</div>

<div class="row">
    <div class="col-lg-6 mb-3">
       <div>
        <x-label for="contact_name">
            {{ ucwords(__('translation.product_name')) }} <x-form.required-mark/>
        </x-label>
        <x-input type="text" name="product_name" id="product_name" value="" />
    </div>
</div>
<div class="col-lg-6 mb-3">
   <div>
    <x-label for="contact_name">
        {{ ucwords(__('translation.product_code')) }} <x-form.required-mark/>
    </x-label>
    <x-input type="text" name="product_code" id="product_code" value="" />
</div>
</div>
</div>

<div class="row">
    <div class="col-lg-6 mb-3">
        <div class="row">
        <div class="col-lg-6">
            <div>
                <x-label for="contact_name">
                    {{ ucwords(__('translation.Regular Price')) }} 
                </x-label>
               <x-input type="text" name="regular_price" id="regular_price" value="" />
            </div>
        </div>
        
        <div class="col-lg-6">
            <div>
                <x-label for="contact_name">
                    {{ ucwords(__('translation.Sale Price')) }}
                </x-label>
                <x-input type="text" name="sale_price" id="sale_price" value="" />
            </div>
        </div>
    </div>
    </div>
    <div class="col-lg-6 mb-3">
       <div>
        <x-label for="contact_name">
            {{ ucwords(__('translation.quantity')) }} <x-form.required-mark/>
        </x-label>
        <x-input type="text" name="quantity" id="quantity" value="" />
    </div>
</div>
</div>

<div class="row">
    <div class="col-lg-12 mb-3">
       <div>
        <x-label for="description">
            {{ ucwords(__('translation.Description')) }}
        </x-label>
        <x-form.textarea name="description" id="description" rows="4" class="summernote"></x-form.textarea>

    </div>
</div>
</div>
<div class="row">
    <div class="col-lg-12 mb-3">
       <div>
        <x-label for="short_description">
            {{ ucwords(__('translation.Short Description')) }}
        </x-label>
        <x-form.textarea name="short_description" id="short_description" name="short_description" rows="4" class="summernote"></x-form.textarea>
    </div>
</div>
</div>

<div class="row">
    <div class="col-lg-12 mb-3">
        <x-label class="">
          <strong>{{__('translation.Linked with Catelog Product')}}</strong>
      </x-label>
      <div class="form-group padding_top_1">
         <x-select class="form-control" id="product_list">
          <option value="">Select Product</option>
          @foreach($products as $key=>$row)
          <option value="{{$row->id}}">{{$row->product_name}}</option>
          @endforeach
      </x-select>
      <input type="hidden" id="catelog_product_id" name="catelog_product_id">
  </div>
</div>
</div>
<div class="row mb-3">
    <div class="col-lg-6 mb-3">
     <div>
        <x-label for="upload_image">
            {{ ucwords(__('translation.Image Uplaod')) }}
        </x-label>
        <div class="grid grid-cols-6 gap-4 gap-x-8" id="woo_product_cover_images_div">
        </div>
        <input type="file" name="product_images" class="add_woo_cover_image_files" multiple>
    </div>
</div>
</div>

<div id="variations_wrapper"></div>

<div class="text-center pb-4 mt-3 pt-3">
    <button type="button" class="btn btn-success" id="__addMoreVariations">{{ __('translation.Add More') }}</button>
</div>
<div class="text-center pb-4 mt-3 pt-3">
    <a href="{{url('wc_products')}}">
        <x-button type="button" color="gray" class="mr-1" id="__btnCancelCreateOrder">
            {{ __('translation.Cancel') }}
        </x-button>
    </a>
    <x-button type="submit" color="blue" id="__btnSubmitCreateOrder">
        {{ __('translation.Create Product') }}
    </x-button>
    <a class="hidden ml-2" id="loader">
       <div class="spinner-border m-2" role="status">
            <span class="sr-only">Loading...</span>
        </div>
    </a>
</div>

</form>
</x-card.body>
</x-card.card-default>
</div>
@endif

@push('bottom_js')
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.19/dist/sweetalert2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote.min.js"></script>
<script type="text/javascript">
 const woo_prod_img_update_url = '{{route('woo.product.upload_product_images')}}';
 const woo_pro_id = $("#id").val();
 const url = "{{ url('wc_products') }}";   

 $('#__formEditWooProduct').on('submit', function(event) {
    event.preventDefault();
    let formData = new FormData($(this)[0]);

    let regular_price = $("#regular_price").val();
    let sale_price = $("#sale_price").val();

    if(sale_price.length>0 && sale_price>0 && sale_price != null){
        if(regular_price.length === 0){
            alert("Please add a regular price first.");
            return false;
        }
    }

    if(regular_price.length > 0 && regular_price>0 && sale_price.length && sale_price>0){

        if(Number(sale_price)>Number(regular_price) || Number(sale_price) === Number(regular_price)){
            alert("Sale price must be less than regular price.");
            return false;
        }

    }

    if(regular_price.length>0 && regular_price>0 && regular_price != null){
        if(sale_price.length === 0){
            alert("Please add a Sale price.");
            return false;
        }
    }

    if (woo_product_cover_image_files.length === 0) {
        alert("Need at least 1 image");
        return;
    } else if (woo_product_cover_image_files.length > 6) {
        alert("At most 6 images can be uploaded");
        return;
    }

    $.each(woo_product_cover_image_files, function(index, file) {
        formData.append('cover_image_'+index, file);
    });
    formData.append('cover_images_count', woo_product_cover_image_files.length);
    
    $.ajax({
        type: 'POST',
        url: '{{ route('wc_products.store') }}',
        data: formData,
        processData: false,
        contentType: false,
        beforeSend: function() {
            $('.alert').addClass('hidden');
            $('#__btnCancelCreateOrder').attr('disabled', true);
            $('#__btnSubmitCreateOrder').attr('disabled', true).html('Submitting');
            $("#loader").removeClass('hidden');

        },
        success: function(responseData) {
            $("#loader").addClass('hidden');
            $('#__btnCancelCreateOrder').attr('disabled', false);
            $('#__btnSubmitCreateOrder').attr('disabled', false).html('Create Order');
            Swal.fire({
                toast: true,
                icon: 'Success',
                title: 'Succcess',
                text: responseData.message,
                timerProgressBar: true,
                timer: 2000,
                position: 'top-end'
            });
           // ridectURL(url);
        },
        error: function(error) {
            let responseJson = error.responseJSON;

            $('html, body').animate({
                scrollTop: 0
            }, 500);

            $('#__btnCancelCreateOrder').attr('disabled', false);
            $('#__btnSubmitCreateOrder').attr('disabled', false).html('Create Order');

            if (error.status == 422) {
                let errorFields = Object.keys(responseJson.errors);
                errorFields.map(field => {
                    $('#__alertDangerContent').append(
                        $('<span/>', {
                            class: 'block mb-1',
                            html: `- ${responseJson.errors[field][0]}`
                        })
                        );
                });

            } else {
                $('#__alertDangerContent').html(responseJson.message);

            }

            $('#__alertDanger').removeClass('hidden');
        }
    });

    return false;
});

            // delete product image individually
            $(document).on('click', '.remove_woo_product_image', function() {
                let conf = confirm("Are you sure you want to remove this image?");
                if (!conf) {
                    return;
                }

                var id = $('#id').val();
                if (typeof(id) === "undefined" || id === "") {
                    alert("Product ID not valid");
                    return;
                }
                var image = $(this).closest('.preview_image_div').children('img').attr('src');

                if (typeof(image) == 'undefined' || image === '') {
                    return;
                }

                var website_id = $('#website_id').val();
                if (typeof(website_id) === "undefined" || website_id === "") {
                    return;
                }
                var formData = new FormData();
                formData.append('id', id);
                formData.append('website_id', website_id);
                formData.append('image', image);
                $.ajax({
                    type: 'POST',
                    url: '{{ route("woo.product.delete_product_images") }}',
                    data: formData,
                    processData: false,
                    contentType: false,
                    beforeSend: function() {
                        $('.spinner-border').removeClass('hidden');
                    },
                    success: function(responseData) {
                        console.log(responseData);
                        Swal.fire({
                            toast: true,
                            icon: 'success',
                            title: 'Succcess',
                            text: responseData.message,
                            timerProgressBar: true,
                            timer: 2000,
                            position: 'top-end'
                        });
                        ridectURL(url);
                    },
                    error: function(error) {
                        Swal.fire({
                            toast: true,
                            icon: 'success',
                            title: 'Couldn\'t reach Product\'s Shop website, please try again',
                            text: error.message,
                            timerProgressBar: true,
                            timer: 2000,
                            position: 'top-end'
                        });
                    }


                });

                return false;   
            });

             // upload product image in woo store
             var woo_product_cover_image_files = [];
             var cover_images_counter = 0;
             $(document).on("change", ".add_woo_cover_image_files", function(el) {
                let image_files = $(this).get(0).files;
                $.each(image_files, function (index, file) {
                    woo_product_cover_image_files.unshift(file);
                });
                
                let html = "";
                $.each(woo_product_cover_image_files, function (index, file) {
                    html += `<div class="mb-5 add_preview_image_div">

                    <img width="100" height="100" src="{{asset('img/No_Image_Available.jpg')}}" alt="image" id="shopee_product_cover_image_`+index+`" class="mb-3 margin_left_12"/>
                    </div>`;
                    var reader = new FileReader();
                    reader.onload = function() {
                        $("#shopee_product_cover_image_"+index).attr("src", reader.result);
                    }
                    reader.readAsDataURL(file);
                });

                if (html.length > 0) {
                    $("#woo_product_cover_images_div").html(html);
                }
            });

             function ridectURL(url){
                if(url !== "undefined"){
                    $('.spinner-border').addClass('hidden');
                    window.location.href = url;
                }
            }

            $('#product_list').select2({
                width: '100%',
                ajax: {
                    type: 'GET',
                    url: '{{ route('product_list.select') }}',
                    data: function(params) {
                        return {
                            search: params.term,
                            page: params.page || 1
                        };
                    },
                    delay: 500
                }
            });

            $('#product_list').on('change', function(event) {
                let product_id = $("#product_list").val();
                $("#catelog_product_id").val(product_id);
            })

        </script>

        <script>
            $(document).ready(function() {
                $('.summernote').summernote({
                    height: 200,
                    placeholder: 'Write Here...',
                });
            });

            $('body').on('change', '#product_type', function() {
                let product_type = $("#product_type").val();
                if(product_type !== ""){
                    let sl = 1;
                    if(product_type === 'simple'){
                        $("#variations_wrapper").html('');
                    }
                    else{
                        addVariations();
                    }
                }
               
            });

            function addVariations(){
                let html = `
                <div class="new_variation mb-3 border-radius-5 bg-gray">
                    <h6>
                    <strong>
                        New Variation
                        <button type="button" class="btn btn-danger btn-sm pull-right float-right remove_variations">Remove</button>
                    </strong>

                 
                    </h6>
                    </hr>
                    <div class="row">
                        <div class="col-lg-6 mb-3">
                         <div class="row">
                            <x-label for="contact_name">
                                {{ ucwords(__('translation.variation code')) }} <x-form.required-mark/>
                            </x-label>
                            <x-input type="text" name="variation_code[]" value="" />
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-lg-6 mb-3">
                           <div>
                            <x-label for="contact_name">
                                {{ ucwords(__('translation.price')) }} <x-form.required-mark/>
                            </x-label>
                            <x-input type="text" name="variation_price[]" value="" />
                            </div>
                        </div>
                        <div class="col-lg-6 mb-3">
                           <div>
                            <x-label for="contact_name">
                                {{ ucwords(__('translation.stock')) }} <x-form.required-mark/>
                            </x-label>
                            <x-input type="text" name="variation_quantity[]" value="" />
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-6 mb-3">
                           <div>
                            <x-label for="contact_name">
                                {{ ucwords(__('translation.Color')) }} <x-form.required-mark/>
                            </x-label>
                            <x-input type="text" name="color[]" value="" />
                            </div>
                        </div>
                        <div class="col-lg-6 mb-3">
                           <div>
                            <x-label for="contact_name">
                                {{ ucwords(__('translation.Size')) }} <x-form.required-mark/>
                            </x-label>
                            <x-input type="text" name="size[]" id="" value="" />
                            </div>
                        </div>
                    </div>
                </div>
                `;
                $("#variations_wrapper").append(html);
            }

            $(document).on('click', '#__addMoreVariations', function() {
                addVariations();
            });

            $(document).on('click', '.remove_variations', function() {
                $(this).closest(".new_variation").remove();
            });

        </script>
        @endpush
    </x-app-layout>
