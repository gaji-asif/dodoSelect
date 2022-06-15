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
        Edit Product #{{$wooProduct->product_id}}

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
    <x-input class="bg-gray-200" type="hidden" name="id" id="id" value="{{$wooProduct->id}}"/>
        <x-input class="bg-gray-200" type="hidden" name="product_id" id="product_id" value="{{$wooProduct->product_id}}"/>
            <x-input class="bg-gray-200" type="hidden" name="website_id" id="website_id" value="{{$wooProduct->website_id}}"/>
                <div class="row mb-3">
                    <div class="col-lg-6">
                       <div>
                        <x-label for="contact_name">
                            {{ __('translation.Shop Name') }}
                        </x-label>
                        <x-input class="bg-gray-200" type="text" name="" id="" value="{{$wooProduct->woo_shop->shops->name}}" readonly/>
                        </div>
                    </div>
                    <div class="col-lg-6">
                       <div>
                        <x-label for="contact_name">
                            {{ __('translation.Status') }}
                        </x-label>
                        <select class="form-control" id="status" name="status">
                            <option value="">Select Status</option>
                            <option value="{{$wooProduct->status}}" @if($wooProduct->status == 'publish') selected @endif>Publish</option>
                            <option value="{{$wooProduct->status}}" @if($wooProduct->status == 'draft') selected @endif>Draft</option>
                        </select>
                    </div>
                </div>
            </div>    
            <div class="row mb-3">
                <div class="col-lg-6">
                   <div>
                    <x-label for="contact_name">
                        {{ ucwords(__('translation.product')) . ' ID' }} <x-form.required-mark/>
                    </x-label>
                    <x-input class="bg-gray-200" type="text" name="product_id" id="product_id" value="{{$wooProduct->product_id}}" readonly/>
                    </div>
                </div>
                <div class="col-lg-6">
                   <div>
                    <x-label for="contact_name">
                        {{ ucwords(__('translation.product type')) }} <x-form.required-mark/>
                    </x-label>
                    <x-input class="bg-gray-200" type="text" name="product_type" id="product_type" value="{{$wooProduct->type}}" readonly/>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-6 mb-3">
                   <div>
                    <x-label for="contact_name">
                        {{ ucwords(__('translation.product_name')) }} <x-form.required-mark/>
                    </x-label>
                    <x-input type="text" name="product_name" id="product_name" value="{{$wooProduct->product_name}}" />
                    </div>
                </div>
                <div class="col-lg-6 mb-3">
                   <div>
                    <x-label for="contact_name">
                        {{ ucwords(__('translation.product_code')) }} <x-form.required-mark/>
                    </x-label>
                    <x-input type="text" name="product_code" id="product_code" value="{{$wooProduct->product_code}}" required />
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-6 mb-3">
                 <div class="row">
                     <div class="col-lg-4">
                        <div>
                            <x-label for="contact_name">
                                {{ ucwords(__('translation.Regular Price')) }} 
                            </x-label>
                            <x-input class="" type="number" name="regular_price" id="regular_price" value="{{$wooProduct->regular_price}}" />
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div>
                                <x-label for="contact_name">
                                    {{ ucwords(__('translation.price')) }} <x-form.required-mark/>
                                </x-label>
                                <x-input class="bg-gray-200" type="number" name="price" id="price" value="{{$wooProduct->price}}" readonly/>
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div>
                                    <x-label for="contact_name">
                                        {{ ucwords(__('translation.Sale Price')) }}
                                    </x-label>
                                    <x-input class="" type="number" name="sale_price" id="sale_price" value="{{$wooProduct->sale_price}}" />
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6 mb-3">
                           <div>
                            <x-label for="contact_name">
                                {{ ucwords(__('translation.quantity')) }} <x-form.required-mark/>
                            </x-label>
                            <x-input type="text" name="quantity" id="quantity" value="{{$wooProduct->quantity}}" />
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-lg-12 mb-3">
                         <div>
                            <x-label for="description">
                                {{ ucwords(__('translation.Description')) }}
                            </x-label>
                            <x-form.textarea name="description" id="description" rows="4" class="summernote">{!! $wooProduct->description !!}</x-form.textarea>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-12 mb-3">
                     <div>
                        <x-label for="short_description">
                            {{ ucwords(__('translation.Short Description')) }}
                        </x-label>
                        <x-form.textarea name="short_description" id="short_description" rows="4" class="summernote">{!! $wooProduct->short_description !!}</x-form.textarea>
                    </div>
                </div>
            </div>

            <div class="row">
                @php $text = ''; @endphp
                @if(isset($wooProduct->dodo_product_id) && $wooProduct->is_linked == 1)
                <div class="col-lg-6 mb-3">
                    <x-label class="">
                      {{__('translation.Catelog Product Name')}}
                  </x-label>
                  <div class="form-group padding_top_1">
                      <strong>{{$wooProduct->catalog->product_name}}</strong><br>
                      {{__('translation.Product Code')}} : 
                      <strong>{{$wooProduct->catalog->product_code}}</strong>
                  </div>
              </div>
              @php  $text = __('translation.Update Catelog Product'); @endphp
              @else
              @php  $text = __('translation.Link with Catelog Product'); @endphp
              @endif
              <div class="col-lg-6 mb-3">
                <x-label class="">
                  {{$text}}
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

  <div class="row">
    <div class="col-lg-12 mb-1">
       <div class="mb-1">
        <x-label for="description">
            {{ ucwords(__('translation.Product Images')) }}
            <div class="spinner-border hidden" role="status">
              <span class="sr-only">Loading...</span>
          </div>
      </x-label>
  </div>
  <div class="">
    <div class="row">
        @if(isset($imageResources))
        @foreach($imageResources as $value)
        @if(!empty($value))
        <div class="col-lg-2 mb-3 text-center preview_image_div">
            <img src="{{ $value->src }}" alt="{{ $value->alt }}" class="w-full h-auto rounded-md">
            <p class="mt-2">
                <button  type="button" class="remove_woo_product_image btn btn-danger btn-sm action_btns" ><i class="fa fa-trash mr-1" aria-hidden="true"></i>Remove</button>
            </p>
        </div>
        @endif
        @endforeach
        @endif
    </div>
</div>
</div>

</div>

<div class="row mb-3">
    <div class="col-lg-6 mb-3">
       <div>
        <x-label for="description">
            {{ ucwords(__('translation.Image Uplaod')) }}
        </x-label>
        <input type="file" name="product_images" id="upload_new_woo_product_img">
    </div>
</div>
</div>
@php $x = 1; @endphp
@if(isset($wooProductsVariations))
@foreach($wooProductsVariations as $value)
<div class="variations_wrapper mb-3 border-radius-5 bg-gray" id="variations_wrapper_{{$value->id}}">
    <h6><strong>Variations # {{$x++}}</strong></h6>
    <hr>
    <div class="row">
        <div class="col-lg-6 mb-3">
         <div>
            <x-label for="contact_name">
                {{ ucwords(__('translation.Variation SKU')) }}
            </x-label>
            <x-input type="hidden" name="product_id_variation[]" id="product_id_variation{{$value->product_id}}" value="{{$value->product_id}}" />
                <x-input type="text" name="product_code_variation[]" id="product_code_variation{{$value->id}}" value="{{$value->product_code}}" />
                </div>
            </div>
            <div class="col-lg-6 mb-3">
               <div>
                <x-label for="contact_name">
                    {{ ucwords(__('translation.stock')) }}
                </x-label>
                <x-input type="text" name="quantity_variation[]" id="quantity_variation{{$value->id}}" value="{{$value->quantity}}" />
                </div>
            </div>
        </div>


        <div class="row mb-3">
         <div class="col-lg-4">
            <div>
                <x-label for="contact_name">
                    {{ ucwords(__('translation.Regular Price')) }} 
                </x-label>
                <x-input class="" type="number" name="regular_price_variation[]" value="{{$value->regular_price}}" />
                </div>
            </div>
            <div class="col-lg-4">
                <div>
                    <x-label for="contact_name">
                        {{ ucwords(__('translation.price')) }} <x-form.required-mark/>
                    </x-label>
                    <x-input class="" type="number" name="price_variation[]" value="{{$value->price}}" readonly/>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div>
                        <x-label for="contact_name">
                            {{ ucwords(__('translation.Sale Price')) }}
                        </x-label>
                        <x-input class="" type="number" name="sale_price_variation[]" value="{{$value->sale_price}}" />
                        </div>
                    </div>
                </div>

                <!-- @php
                $color_value = '';
                $size_value = '';
                @endphp

                @if($getProductAttributes['attribute_1'] == 'color')
                @php 
                $color_value = $getProductAttributes['attribute_1_option'];
                $size_value = $getProductAttributes['attribute_2_option'];
                @endphp
                @endif
                @if($getProductAttributes['attribute_1'] == 'Size')
                @php $size_value = $getProductAttributes['attribute_1_option']; @endphp
                @endif -->

                <!-- <div class="row">
                    <div class="col-lg-6 mb-3">
                   <div>
                    <x-label for="contact_name">
                        {{ ucwords(__('translation.Color')) }}
                    </x-label>
                    <x-input type="text" name="color" id="color{{$value->id}}" value="{{$color_value}}" />
                </div>
                </div>
                <div class="col-lg-6 mb-3">
                   <div>
                    <x-label for="contact_name">
                        {{ ucwords(__('translation.Size')) }}
                    </x-label>
                    <x-input type="text" name="size" id="size{{$value->id}}" value="{{$size_value}}" />
                </div>
            </div> -->
            <div class="row">
                <div class="col-lg-12 mb-3">
                   <div class="mb-1">
                    <x-label for="description">
                        {{ ucwords(__('translation.Variation Specific Images')) }}
                    </x-label>
                </div>
                <div class="">
                    <div class="row">

                        @php
                        $imageResourcesForVariations = json_decode($value->images);
                        @endphp

                        <div class="col-lg-2 mb-3 text-center">
                            @if(!empty($imageResourcesForVariations[0]))
                            <img src="{{ $imageResourcesForVariations[0]->src }}" alt="{{ $value->alt }}" class="w-full h-auto rounded-md">
                            @endif
            <!-- <p class="">
                <button  type="button" class="shipment_btns btn btn-danger btn-sm action_btns" ><i class="fa fa-trash mr-1" aria-hidden="true"></i>Remove</button>
            </p> -->
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-lg-6 mb-3">
         <div>
            <x-label for="description">
                {{ ucwords(__('translation.Image Uplaod')) }}
            </x-label>
            <input type="file" class="woo_product_img_variation" name="woo_product_img_variation[]">
        </div>
    </div>
</div>
</div>
</div>
</div>
</div>

@endforeach
@endif
<div class="text-center pb-4 mt-3 pt-3">
    <a href="{{url('wc_products')}}">
        <x-button type="button" color="gray" class="mr-1" id="__btnCancelCreateOrder">
            {{ __('translation.Cancel') }}
        </x-button>
    </a>
    <x-button type="submit" color="blue" id="__btnSubmitCreateOrder">
        {{ __('translation.Update Info') }}
    </x-button>
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

 const woo_website_id = $("#website_id").val();
 const woo_pro_id = $("#product_id").val();
 const url = "{{ url('wc_products') }}"+'/'+woo_website_id+'/'+woo_pro_id;

 $('#__formEditWooProduct').on('submit', function(event) {
    event.preventDefault();
    let woo_pro_id = $("#product_id").val();
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

    console.log(formData);

    $.ajax({
        type: 'POST',
        url: '{{ route('wc_products.update') }}',
        data: formData,
        processData: false,
        contentType: false,
        beforeSend: function() {
            $('#__btnSubmitCreateOrder').attr('disabled', true).html('Processing');
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
            $('#__btnSubmitCreateOrder').attr('disabled', false).html('Processing');
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
                        $('#__btnSubmitCreateOrder').attr('disabled', false).html('Update');
                        
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
             $(document).on('change', '#upload_new_woo_product_img', function () {
                //var preview_div = $("#edit_preview_image_div");

                var id = $('#id').val();
                if (typeof(id) === "undefined" || id === "") {
                    alert("Product Id not valid");
                    return;
                }

                var website_id = $('#website_id').val();
                if (typeof(website_id) === "undefined" || website_id === "") {
                    return;
                }

                var product_type = $('#product_type').val();
                if (typeof(product_type) === "undefined" || product_type === "") {
                    return;
                }

                var file = $("#upload_new_woo_product_img").get(0).files[0];
                if(file) {
                    var formData = new FormData();
                    formData.append('id', id);
                    formData.append('image', $("#upload_new_woo_product_img").get(0).files[0]);
                    formData.append('website_id', website_id);
                    formData.append('product_type', product_type);
                    formData.append('_token', $('meta[name=csrf-token]').attr('content'));
                    $.ajax({
                        type: 'POST',
                        url: woo_prod_img_update_url,
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
                }
            });

             function ridectURL(url){
                if(url !== "undefined"){
                    $('.spinner-border').addClass('hidden');
                    window.location.href = url;
                }
            }

            $('#product_list').on('change', function(event) {
                let product_id = $("#product_list").val();
                $("#catelog_product_id").val(product_id);
            })

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
        </script>
        <script>
            $(document).ready(function() {
                $('.summernote').summernote({
                    height: 200
                    
                });
            });
        </script>
        @endpush
    </x-app-layout>
