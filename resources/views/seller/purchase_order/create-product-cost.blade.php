<x-app-layout>
    @section('title', 'Purchase Order')

    @push('top_css')
        <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css">

        <style type="text/css">
        table
        {
            border: 1px solid #ccc;
            border-collapse: collapse;
        }
        table th
        {
            background-color: #F7F7F7;
            color: #333;
            font-weight: bold;
        }
        table th, table td
        {
            padding: 5px;
            border: 1px solid #ccc;
        }

            a:hover {
                text-decoration: none;
            }

            @media screen and (max-width: 600px) {
                .pricing_title {
                    margin-top: 60px;
                }
            }

            .card-header h2 {
                font-size: 22px;
                font-weight: bold !important;
            }

            .lead {
                font-size: 18px;
            }

            .order_quantity {
                width: 53px;
                border: 1px solid;
                padding: 2px;
                text-align: center;
                padding-left: 7px;
            }

            a:hover {
                text-decoration: none;
            }

            .hide {
                display: none;
            }

            .cutome_image {
                height: 70px;
                width: 100px
            }

            /* .select2-container .select2-selection--single {
                height: 36px; */
                /* border-color: rgba(209,213,219,var(--tw-border-opacity)); */
            /* } */

            /* .select2-container--default .select2-selection--single .select2-selection__rendered {
                line-height: 36px;
            } */

            .card-title {
                font-size: 18px;
                margin-bottom: 10px;
            }

            .loading {
                display: inline-block;
                vertical-align: middle;
                width: 16px;
                height: 16px;
                /* background-color: #F0F0F0; */
                position: absolute;
                right: 34px;
                top: 75px;
            }

            /* Example #1 */
            #autocomplete.ui-autocomplete-loading~#loading1 {
                background-image: url(//cdn.rawgit.com/salmanarshad2000/demos/v1.0.0/jquery-ui-autocomplete/loading.gif);
            }

            /* Example #2 */
            #loading2.isloading {
                background-image: url(//cdn.rawgit.com/salmanarshad2000/demos/v1.0.0/jquery-ui-autocomplete/loading.gif);
            }
        </style>
    @endpush

    <div class="col-span-12">

        @if ($errors->any())
            <x-alert-danger class="mb-5">
                <ul class="mt-3 list-disc list-inside text-sm text-red-600">
                    @foreach ($errors->all() as $error)
                        <li>
                            {{ $error }}
                        </li>
                    @endforeach
                </ul>
            </x-alert-danger>
        @endif

        <x-alert-danger class="alert mb-5 hidden" id="__alertDanger">
            <span id="__content_alertDanger"></span>
        </x-alert-danger>

        <x-alert-success class="alert mb-5 hidden" id="__alertSuccess">
            <span id="__content_alertSuccess"></span>
        </x-alert-success>

        <form method="POST" action="{{ route('store product cost') }}" id="__formPurchaseOrder" enctype="multipart/form-data">
            @csrf
            <div>
                <div class="grid grid-cols-1 lg:grid-cols-1 lg:gap-x-5">
                    <div>
                        <x-card.card-default>
                            <x-card.header>
                                <x-card.title>
                                    {{ __('translation.Product Info') }} sss
                                </x-card.title>
                            </x-card.header>
                            <x-card.body>
                            <div class="flex flex-row mb-5 py-4">
                                <div class="w-1/4 sm:w-1/4 md:w-1/6 mb-4 md:mb-0">
                                    <div class="mb-4">
                                        <img src='{{asset($product->image)}}' alt="Image" class="w-full h-auto rounded-sm">
                                    </div>
                                </div>
                                <div class="w-3/4 sm:w-3/4 md:w-5/6 ml-4 sm:ml-6">
                                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-2 gap-4 sm:gap-x-6 lg:pt-1">
                                        <div>

                                            <div class="mb-2 xl:mb-4 lg:col-span-2 xl:col-span-3">
                                                <label class="hidden lg:block mb-0">
                                                    {{ __('translation.Product Name') }} :
                                                </label>
                                                <p class="font-bold">
                                                    {{$product->product_name}} <br>
                                                    <span class="text-blue-500">{{$product->product_code}}</span>
                                                </p>
                                            </div>
                                            <div class="grid grid-cols-1 lg:grid-cols-3 gap-3">
                                                <div>
                                                    <label class="mb-0">
                                                        {{ __('translation.Price') }} :
                                                    </label>
                                                    <span class="font-bold lg:block">
                                                        {{ currency_symbol('THB') }}
                                                        {{$product->price}}
                                                    </span>
                                                </div>
                                                <div>
                                                    <label class="mb-0">
                                                        {{ __('translation.Pieces/Pack') }} :
                                                    </label>
                                                    <span class="font-bold lg:block">
                                                        {{$product->pack}}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            </x-card.body>
                        </x-card.card-default>
                    </div>
                </div>
                <div>

                <div class="grid grid-cols-1 lg:grid-cols-1 lg:gap-x-5">
                    <div>
                        <x-card.card-default>                            
                            <x-card.header>
                                <x-card.title>
                                {{ __('translation.Lowest Sell Price') }}
                                </x-card.title>
                            </x-card.header>

                            <x-card.body>

                            
                            <div class="w-1/4 sm:w-3/4 md:w-1/4 ml-4 sm:ml-6">
                                <label class="mb-0">
                                    {{ __('translation.Select By') }} :
                                </label>
                                <div class="w-full">
                                    <x-select id="lowest_is_type" data-product-id="{{$product->id}}"  name="lowest_is_type">
                                        <option value="0" @if($product->lowest_is_type=='0') selected @endif > {{  __('translation.Manual')  }} </option>
                                        <option value="1" @if($product->lowest_is_type=='1') selected @endif> {{  __('translation.By %')  }} </option>
                                    </x-select>
                                </div>
                            </div>

                            <div class="w-1/4 sm:w-3/4 md:w-1/4 ml-4 sm:ml-6 mt-4">
                                <div class="">
                                    <label class="mb-0">
                                        {{ __('translation.Lowest Sell Price') }} :
                                    </label>
                                    <x-input  type="number" data-product-id="{{$product->id}}" step="0.01" name="lowest_value" id="lowest_value" value="{{$product->lowest_value}}"> </x-input>  
                                    <x-input  type="hidden" data-product-id="{{$product->id}}"  name="lowest_sell_price" id="lowest_sell_price" value="{{$product->lowest_sell_price}}">  </x-input>                                                     
                                    <x-input  type="hidden" data-product-id="{{$product->id}}"  name="profit" id="profit" value="{{$product->profit}}">  </x-input>                                                     
                                    <x-input  type="hidden" data-product-id="{{$product->id}}"  name="mark_up" id="mark_up" value="{{$product->mark_up}}">  </x-input>                                                     
                                </div>
                            </div>

                            <div id="__profitCalculationWrapper" class="w-full mt-3"> 

                            
                                <div class="w-full sm:ml-6">
                                    <label class="mb-0">
                                        <strong> {{ __('translation.Lowest Sell Price') }} : </strong> {{$product->lowest_sell_price}}
                                    </label>
                                </div>

                                <div class="w-full sm:ml-6">
                                    <label class="mb-0">
                                    <strong> {{ __('translation.Profit') }} (THB) : </strong> {{$product->profit}}
                                    </label>
                                </div>

                                <div class="w-full sm:ml-6">
                                    <label class="mb-0">
                                    <strong> {{ __('translation.Mark Up') }} (%) : </strong> {{$product->mark_up}}
                                    </label>
                                </div>

                           
                            </div>


                            <div hidden id="__templateProfitCalculationWrapper">
                                <div class="w-full sm:ml-6">
                                    <label class="mb-0">
                                        <strong> {{ __('translation.Lowest Sell Price') }} : </strong> {lowest_sell_price}
                                    </label>
                                </div>

                                <div class="w-full sm:ml-6">
                                    <label class="mb-0">
                                    <strong> {{ __('translation.Profit') }} (THB) : </strong> {profit}
                                    </label>
                                </div>

                                <div class="w-full sm:ml-6">
                                    <label class="mb-0">
                                    <strong> {{ __('translation.Mark Up') }} (%) : </strong> {mark_up}
                                    </label>
                                </div>

                            </div>

                            </x-card.body>
                        </x-card.card-default>
                    </div>
                </div>


                    <x-card.card-default>
                        <x-card.header>
                            <x-card.title>
                                {{ __('translation.Product Cost') }}
                            </x-card.title>
                            <x-card.right-action>
                                <x-button type="button" style="margin-right:15%" data-product-code="{{$product->product_code}}" color="green" id="__btnAddSupplier">
                                    {{ __('translation.Add') }}
                                </x-button>
                            </x-card.right-action>
                            <x-card.right-action>
                                <x-button type="button" color="yellow" id="__btnClearProductList">
                                    {{ __('translation.Clear') }}
                                </x-button>
                            </x-card.right-action>
                        </x-card.header>
                        <x-card.body>
                            <hr class="w-full border border-dashed border-r-0 border-b-0 border-l-0 border-blue-500 mb-5">
                            <div class="mb-0">
                                <div id="__wrapper_ProductList"></div>
                                <div id="__wrapper_NoProduct">
                                    <div class="w-full p-0 rounded-lg text-center">
                                        <span class="font-bold text-lg text-gray-500">
                                        @if (empty($product_costs)) No Product Added @endif
                                        </span>
                                    </div>
                                </div>
                            </div>

                             @if (isset($product_costs))
                                @foreach ($product_costs as $product_cost)
                            <div class="hidden1" id33="__templateProductItem22"  id="__row_ProductItem_{{$product_cost->id}}">
                                <div class="flex flex-row mb-5 py-4 border border-solid border-t-0 border-r-0 border-l-0 border-gray-200">
                                    <input type="hidden" name="product_id[]" value="{{$product_cost->product_id}}">
                                    <div class="w-full sm:w-3/4 md:w-5/6 ml-4 sm:ml-6">
                                        <div class="grid grid-cols-1 sm:grid-cols-1 md:grid-cols-1 lg:grid-cols-1 gap-4 sm:gap-x-6 lg:pt-1">

                                            <div class="md:col-span-2 lg:col-span-1">
                                                <div class="mb-4">
                                                <div>
                                                    <label class="text-l text-gray-800 font-bold leading-tight relative mb-2">
                                                        <input type="radio" id="choose_supplier" name="choose_supplier" value="0" @if($product_cost->default_supplier == 1) checked @endif>  {{ __('translation.Supplier Choice') }}
                                                        <input type="hidden" class="default_supplier" name="default_supplier[]" value="{{$product_cost->default_supplier}}">
                                                    </label>
                                                </div>
                                                <div class="grid grid-cols-3 md:grid-cols-3 gap-4">
                                                    <div>
                                                        <label class="mb-0">
                                                            {{ __('translation.Supplier') }} :
                                                        </label>
                                                        <div class="w-full">
                                                            <x-select name="supplier_id[]">
                                                                <option value="" selected disabled>
                                                                    {{ '- ' . __('translation.Select Supplier') . ' -' }}
                                                                </option>
                                                                @foreach ($suppliers as $supplier)
                                                                    <option value="{{ $supplier->id }}" @if($supplier->id == $product_cost->supplier_id) selected @endif>
                                                                        {{ $supplier->supplier_name }}
                                                                    </option>
                                                                @endforeach
                                                            </x-select>
                                                        </div>
                                                    </div>


                                                        <div>
                                                            <label class="mb-0">
                                                                {{ __('translation.Cost Per Piece') }} :
                                                            </label>
                                                            <div class="w-full">
                                                                <x-input type="text" name="cost[]" value="{{$product_cost->cost}}" />
                                                            </div>
                                                        </div>


                                                        <div>
                                                            <label class="mb-0">
                                                                {{ __('translation.Currency') }} :
                                                            </label>
                                                            <div class="w-full">
                                                                <x-select name="exchange_rate_id[]">
                                                                    <option value="" selected disabled>
                                                                        {{ '- ' . __('translation.Select Currency') . ' -' }}
                                                                    </option>
                                                                    @foreach ($exchangeRates as $exchangeRate)
                                                                        <option value="{{ $exchangeRate->id }}" @if($exchangeRate->id == $product_cost->exchange_rate_id) selected @endif>
                                                                            {{ $exchangeRate->name }}
                                                                        </option>
                                                                    @endforeach
                                                                </x-select>
                                                            </div>
                                                        </div>

                                                    </div>
                                                </div>

                                                <div class="mb-4 lg:mb-2">
                                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                                        <div>
                                                            <label class="mb-0">
                                                                {{ __('translation.Pieces Per Pack') }} :
                                                            </label>
                                                            <div class="w-full">
                                                                <x-input type="text" name="pieces_per_pack[]" class="pieces_per_pack" value="{{$product_cost->pieces_per_pack}}" />
                                                            </div>
                                                        </div>


                                                        <div>
                                                            <label>
                                                                {{ __('translation.Pieces Per Carton') }}
                                                            </label>
                                                            <x-input type="text" name="pieces_per_carton[]" class="pieces_per_carton" value="{{$product_cost->pieces_per_carton}}" />
                                                        </div>

                                                        <div>
                                                            <label class="mb-0">
                                                                {{ __('translation.Ship Cost') }} :
                                                            </label>
                                                            <div class="w-full">
                                                                <x-input type="text" name="operation_cost[]" class="operation_cost" value="{{$product_cost->operation_cost}}" />
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>


                                                <div class="mb-4 lg:mb-2">
                                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                                                        <div>
                                                            <label>
                                                                {{ __('translation.Date') }}
                                                            </label>
                                                            <input type="text" name="created_at[]" value='{{date_format($product_cost->created_at,"Y-m-d") }}' class="datepicker w-full h-10 px-3 py-2 rounded-md shadow-sm border border-solid border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 mt-1" />
                                                        </div>

                                                        <div>
                                                            <label class="mb-0">
                                                                {{ __('translation.File') }} :
                                                            </label>
                                                            <div class="w-full">
                                                                <x-input type="file" name="file[]" id="image" onchange="previewFile(this);" class="file" value="" />
                                                            </div>

                                                            @if (!empty($product_cost->file))
                                                                <div id="tableau" style="max-height:100px !important">
                                                                    @php
                                                                        $productCostFile = $product_cost->file;

                                                                        $filePath = str_replace('product-cost','product-cost/', $productCostFile);
                                                                        if (strpos($productCostFile, 'product-cost/') !== false) {
                                                                            $filePath = str_replace('product-cost','product-cost/', $productCostFile);
                                                                        }

                                                                        $filePathExploded = explode('product-cost/', $filePath);
                                                                        $fileName = $filePathExploded[1] ?? 'No-file';
                                                                    @endphp
                                                                    <a href="{{ asset($filePath) }}" data-excel-link="{{ asset($filePath) }}">
                                                                        {{ $fileName }}
                                                                    </a>
                                                                </div>
                                                            @endif

                                                        </div>

                                                        <div class="mb-5"></div>
                                                            <div class="mb-5 hide" id="preview_image_div">
                                                                <x-label>
                                                                    {{ __('translation.Preview Image') }}
                                                                </x-label>
                                                                <img id="previewImg" width="100" height="100" src="{{asset('img/No_Image_Available.jpg')}}" alt="Placeholder">
                                                            </div>


                                                    </div>
                                                </div>


                                                <div class="hidden lg:block text-right lg:text-left lg:mt-5 xl:mt-3">
                                                    <x-button type="button" color="red" class="block lg:relative w-full lg:w-auto" data-id="{{$product_cost->id}}" onClick="removeProductItem(this)">
                                                        {{ __('translation.Remove') }}
                                                    </x-button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                                    @endif



                            <div class="text-center pb-5">
                                <x-button type="submit" color="blue" id="__btnSubmitPurchaseOrder">
                                    {{ __('translation.Submit Data') }}
                                </x-button>
                            </div>
                        </x-card.body>
                    </x-card.card-default>
                </div>
            </div>
        </form>
    </div>

    <div class="hidden" id="__templateProductItem">
        <div class="flex flex-row mb-5 py-4 border border-solid border-t-0 border-r-0 border-l-0 border-gray-200" id="__row_ProductItem_{{$product->product_code}}">
            <input type="hidden" name="product_id[]" value="{{$product->id}}">
            <div class="w-full sm:w-3/4 md:w-5/6 ml-4 sm:ml-6">
                <div class="grid grid-cols-1 sm:grid-cols-1 md:grid-cols-1 lg:grid-cols-1 gap-4 sm:gap-x-6 lg:pt-1">

                    <div class="md:col-span-2 lg:col-span-1">
                        <div class="mb-4">
                        <div>
                            <label class="text-l text-gray-800 font-bold leading-tight relative mb-2">
                                <input type="radio" id="choose_supplier" name="choose_supplier" value="0">  {{ __('translation.Supplier Choice') }}
                                <input type="hidden" class="default_supplier" name="default_supplier[]" value="0">
                            </label>
                        </div>
                        <div class="grid grid-cols-3 md:grid-cols-3 gap-4">
                            <div>
                                <label class="mb-0">
                                    {{ __('translation.Supplier') }} :
                                </label>
                                <div class="w-full">
                                    <x-select name="supplier_id[]">
                                        <option value="" selected disabled>
                                            {{ '- ' . __('translation.Select Supplier') . ' -' }}
                                        </option>
                                        @foreach ($suppliers as $supplier)
                                            <option value="{{ $supplier->id }}">
                                                {{ $supplier->supplier_name }}
                                            </option>
                                        @endforeach
                                    </x-select>
                                </div>
                            </div>


                                <div>
                                    <label class="mb-0">
                                        {{ __('translation.Cost Per Piece') }} :
                                    </label>
                                    <div class="w-full">
                                        <x-input type="text" name="cost[]" value="" />
                                    </div>
                                </div>


                                <div>
                                    <label class="mb-0">
                                        {{ __('translation.Currency') }} :
                                    </label>
                                    <div class="w-full">
                                        <x-select name="exchange_rate_id[]">
                                            <option value="" selected disabled>
                                                {{ '- ' . __('translation.Select Currency') . ' -' }}
                                            </option>
                                            @foreach ($exchangeRates as $exchangeRate)
                                                <option value="{{ $exchangeRate->id }}">
                                                    {{ $exchangeRate->name }}
                                                </option>
                                            @endforeach
                                        </x-select>
                                    </div>
                                </div>

                            </div>
                        </div>


                        <div class="mb-4 lg:mb-2">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <label class="mb-0">
                                        {{ __('translation.Pieces Per Pack') }} :
                                    </label>
                                    <div class="w-full">
                                        <x-input type="text" name="pieces_per_pack[]" class="pieces_per_pack" value="" />
                                    </div>
                                </div>

                                <div>
                                    <label>
                                        {{ __('translation.Pieces Per Carton') }}
                                    </label>
                                    <x-input type="text" name="pieces_per_carton[]" class="pieces_per_carton" value="" />
                                </div>

                                <div>
                                    <label class="mb-0">
                                        {{ __('translation.Ship Cost') }} :
                                    </label>
                                    <div class="w-full">
                                        <x-input type="text" name="operation_cost[]" class="operation_cost" value="" />
                                    </div>
                                </div>
                            </div>
                        </div>


                        <div class="mb-4 lg:mb-2">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                                <div>
                                    <label>
                                        {{ __('translation.Date') }}
                                    </label>
                                    <input type="text" name="created_at[]" class="datepicker w-full h-10 px-3 py-2 rounded-md shadow-sm border border-solid border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 mt-1" />
                                </div>

                                <div>
                                    <label class="mb-0">
                                        {{ __('translation.File') }} :
                                    </label>
                                    <div class="w-full">
                                        <x-input type="file" name="file[]" class="file" value="" />
                                    </div>
                                </div>
                            </div>
                        </div>


                        <div class="hidden lg:block text-right lg:text-left lg:mt-5 xl:mt-3">
                            <x-button type="button" color="red" class="block lg:relative w-full lg:w-auto" data-id="{{$product->product_code}}" onClick="removeProductItem(this)">
                                {{ __('translation.Remove') }}
                            </x-button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <x-modal.alert id="__modalAlert" class="hidden">
        <x-modal.header>
            <x-modal.title>
                {{ __('translation.Info') }}
            </x-modal.title>
            <x-modal.close-button id="__btnClose_modalAlert" />
        </x-modal.header>
        <x-modal.body>
            <div class="text-center pb-10 text-base" id="__content_modalAlert"></div>
        </x-modal.body>
    </x-modal.alert>

    @push('bottom_js')
        <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js"></script>

         <script type="text/javascript">

            function previewFile(input){
                var preview_div = $("#preview_image_div");
                if($(preview_div).hasClass('hide'))
                {
                    $(preview_div).removeClass('hide');
                    $(preview_div).addClass('show');
                }

                var file = $("#image").get(0).files[0];

                if(file){
                    var reader = new FileReader();
                    reader.onload = function(){
                        console.log(reader);
                        $("#previewImg").attr("src", reader.result);
                    }
                    reader.readAsDataURL(file);
                }
            }


            $('body').on('focus',".datepicker", function(){
                $(this).datepicker({
                    dateFormat: 'dd-mm-yy'
                });
            });


            const productSource = {!! $product->toJson() !!};
            var selectedProductsToList = [];

            $(window).on('load', function() {
                $('#__formPurchaseOrder')[0].reset();
            });


            $('#supplier_id').select2({
                width: 'resolve'
            });

            $(document).ready(function() {

                $(document).on('change', 'input[name=choose_supplier]', function(event) {
                    event.preventDefault();
                    var choose_supplier = $("input[name='choose_supplier']:checked").val();
                    $(".default_supplier").val(0);
                    if($("input[name='choose_supplier']").is(':checked')) {
                        $(this).next().val(1);
                    }
                });

                $(document).on('click', '#__btnAddSupplier', function(event) {
                    event.preventDefault();
                    let productCode = $(this).data('product-code');
                    renderSupplierToList(productCode);
                    return false;
                });


                $(document).on('keyup', '#lowest_value', function(event) {
                    event.preventDefault();
                    let productId = $(this).data('product-id');
                    let lowest_is_type = $("#lowest_is_type").val();                    
                    let lowest_value = $(this).val();

                    productCostProfitCalculation(productId,lowest_is_type, lowest_value);
                });

                $(document).on('change', '#lowest_is_type', function(event) {
                    event.preventDefault();
                    let productId = $(this).data('product-id');
                    let lowest_is_type = $(this).val();
                    let lowest_value = $("#lowest_value").val();

                    productCostProfitCalculation(productId,lowest_is_type, lowest_value);
                });


                const productCostProfitCalculation = (productId,lowest_is_type, lowest_value) => {
                    if (productId !== '') {
                    $.ajax({
                        type: 'GET',
                        data: {
                            product_id: productId,
                            lowest_is_type : lowest_is_type,
                            lowest_value : lowest_value
                        },
                        url: '{{ route('product_cost_markup_profit_calculation') }}',
                        success: function(responseJson) {
                            console.log(responseJson);   
                           
                            var lowest_value_input =  $('#lowest_value').val();
                            if(lowest_value_input.length>0){
                                let templateProfitCalculationElement = $('#__templateProfitCalculationWrapper').clone();
                                let lowest_sell_price = parseFloat(responseJson.data.lowest_sell_price).toFixed(2);

                                let mark_up = parseFloat(responseJson.data.mark_up).toFixed(2);
                                if(lowest_is_type==0){
                                    var is_type = 'THB';
                                }else{
                                    var is_type = '%';
                                }
                                let profit = parseFloat(responseJson.data.profit).toFixed(2);

                                
                                $('#lowest_sell_price').val(lowest_sell_price);
                                $('#mark_up').val(mark_up);
                                $('#profit').val(profit);
                                
                                

                                templateProfitCalculationElement.html(function(index, html) {
                                    return html.replaceAll('{lowest_sell_price}', lowest_sell_price);
                                });
                                templateProfitCalculationElement.html(function(index, html) {
                                    return html.replaceAll('{profit}', profit);
                                });

                                templateProfitCalculationElement.html(function(index, html) {
                                    return html.replaceAll('{is_type}', is_type);
                                });

                                templateProfitCalculationElement.html(function(index, html) {
                                    return html.replaceAll('{mark_up}', mark_up);
                                });



                                
                            $('#__profitCalculationWrapper').html(templateProfitCalculationElement.html());
                                
                                
                            }else{
                                
                                $('#__profitCalculationWrapper').html(" ");
                            }
                                
                 

                        },
                        error: function(error) {
                            let responseJson = error.responseJSON;
                        }
                    });


                }
                   
                    //renderSupplierToList(productCode);
                    return false;
                }

            });
        </script>




        <script>






            const renderSupplierToList = ClickValue => {
                productCode = ClickValue;

                if (productCode !== '') {
                    $.ajax({
                        type: 'GET',
                        data: {
                            product_code: productCode
                        },
                        url: '{{ route('get_qr_code_product_order_purchase') }}',
                        success: function(responseJson) {
                            $('#__wrapper_NoProduct').hide();
                            let templateProductItemElement = $('#__templateProductItem').clone();
                            let product = responseJson.data;


                           // if (selectedProductsToList.indexOf(product.product_code) === -1) {
                                selectedProductsToList.push(product.product_code);

                                templateProductItemElement.html(function(index, html) {
                                    return html.replace('src="#"', 'src="'+ product.image_url +'"');
                                });
                                templateProductItemElement.html(function(index, html) {
                                    return html.replaceAll('{product_id}', product.id);
                                });
                                templateProductItemElement.html(function(index, html) {
                                    return html.replaceAll('{product_name}', product.product_name);
                                });
                                templateProductItemElement.html(function(index, html) {
                                    return html.replaceAll('{product_code}', product.product_code);
                                });
                                templateProductItemElement.html(function(index, html) {
                                    return html.replace('{price}', product.price);
                                });
                                templateProductItemElement.html(function(index, html) {
                                    return html.replace('{pack}', product.pack);
                                });
                                templateProductItemElement.html(function(index, html) {
                                    return html.replace('{available_qty}', product.get_quantity.quantity);
                                });

                                $('#__wrapper_ProductList').prepend(templateProductItemElement.html());
                           // }


                            if (selectedProductsToList.indexOf(product.product_code) > -1) {

                                let stockAdjustElement = $(`#__row_ProductItem_${product.product_code} .order-qty__field`);
                                let currentValue = parseInt(stockAdjustElement.val());

                                let increasedValue = currentValue + 1;
                                stockAdjustElement.val(increasedValue);
                            }

                        },
                        error: function(error) {
                            let responseJson = error.responseJSON;

                            $('#__modalAlert').removeClass('hidden');
                            $('body').addClass('modal-open');

                            $('#__content_modalAlert').html(null);
                            $('#__content_modalAlert').html(responseJson.message);
                        }
                    });


                }
            }


            const removeProductItem = el => {
                const dataId = el.getAttribute('data-id');
                selectedProductsToList.splice(selectedProductsToList.indexOf(dataId), 1);

                $(`#__row_ProductItem_${dataId}`).remove();

                if (selectedProductsToList.length === 0) {
                    $('#__wrapper_NoProduct').show();
                }
            }


            $('#__btnClearProductList').click(function() {
                selectedProductsToList = [];

                $('#__wrapper_ProductList').html(null);
                $('#__wrapper_NoProduct').show();
            });


            $('#__formPurchaseOrder').submit(function(event) {
                event.preventDefault();

                let formData = new FormData($(this)[0]);

                $.ajax({
                    type: $(this).attr('method'),
                    url: $(this).attr('action'),
                    data: formData,
                    processData: false,
                    contentType: false,
                    beforeSend: function() {
                        $('#__btnSubmitPurchaseOrder').attr('disabled', true).html('{{ __('translation.Processing') }}');

                        $('.alert').addClass('hidden');
                    },
                    success: function(responseJson) {
                        // $('#__btnSubmitPurchaseOrder').attr('disabled', false).html('{{ __('translation.Submit Data') }}');

                        $('html, body').animate({
                            scrollTop: 0
                        }, 500);

                        $('.alert').addClass('hidden');
                        $('#__alertSuccess').removeClass('hidden');
                        $('#__content_alertSuccess').html(null);
                        $('#__content_alertSuccess').html(responseJson.message);

                        setTimeout(() => {
                            //window.location.href = '{{ route('product_cost') }}';
                            location.reload();
                        }, 1500);
                    },
                    error: function(response) {
                        let responseJson = response.responseJSON;
                       //console.log(responseJson);

                        $('#__btnSubmitPurchaseOrder').attr('disabled', false).html('{{ __('translation.Submit Data') }}');

                        $('.alert').addClass('hidden');
                        $('#__alertDanger').removeClass('hidden');
                        $('#__content_alertDanger').html(null);

                        $('html, body').animate({
                            scrollTop: 0
                        }, 500);

                        if (response.status == 422) {
                            let errorFields = Object.keys(responseJson.errors);
                            errorFields.map(field => {
                                $('#__content_alertDanger').append(
                                    $('<div/>', {
                                        html: responseJson.errors[field][0],
                                        class: 'mb-2'
                                    })
                                );
                            });
                        }
                        else {
                            $('#__content_alertDanger').html(responseJson.message);
                        }
                    }
                });

                return false;
            });
        </script>
    @endpush

</x-app-layout>
