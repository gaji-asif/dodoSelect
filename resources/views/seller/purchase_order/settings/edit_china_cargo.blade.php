<x-app-layout>
    @section('title', 'China Cargo')

    @push('top_css')
	<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>


	<link href="https://cdn.datatables.net/1.10.21/css/jquery.dataTables.min.css" rel="stylesheet">
        <link href="https://cdn.datatables.net/1.10.21/css/dataTables.bootstrap4.min.css" rel="stylesheet">
        <script src="https://cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js"></script>
        <script src="https://cdn.datatables.net/1.10.21/js/dataTables.bootstrap4.min.js"></script>

        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">

        <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
        <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

        <!-- Fonts -->
        <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap">
        <style type="text/css">

        tr.parent td {
            background-color: #5384d52b;
            box-shadow: inset -1px -18px 20px 20px #bdc3ce45;
            font-weight: bold;
            font-size: 20px;
        }


        .subcategory {width: 70% !important;     box-shadow: none !important;}
        .subcategory td{    background: #fff;}
        .subcategory td:first-child{padding-left: 5rem !important;}
        </style>
    @endpush

    @if(\App\Models\Role::checkRolePermissions('Can access menu: Purchase Order - Settings'))
    <x-card class="mt-0">
        <div class="" style="margin-top: -2rem">
            @include('seller.purchase_order.settings.menu')
        </div>
        <hr>

        <card class="bg-gray-500 ">


            <div class="flex justify-between flex-col">

                <div class="overflow-x-auto">
                    <x-alert-danger class="alert mb-5 hidden" id="__alertDanger">
                    <span id="__content_alertDanger"></span>
                    </x-alert-danger>

                    <x-alert-success class="alert mb-5 hidden" id="__alertSuccess">
                        <span id="__content_alertSuccess"></span>
                    </x-alert-success>
                <form method="POST" action="{{ route('update china cargo') }}" id="__formChinaCargo" enctype="multipart/form-data">
            @csrf
            <div>
                <div class="grid grid-cols-1 lg:grid-cols-1 lg:gap-x-5">
                    <div>

                            <x-card.header>
                                <x-card.title>
                                    {{ __('translation.China Cargo') }}
                                </x-card.title>

                            </x-card.header>
                            <x-card.body>
                            <div class="flex flex-row mb-5 py-4">
                                <div class="w-full sm:w-full md:w-5/6">
                                    <div class="grid grid-cols-1 sm:grid-cols-1 md:grid-cols-1 lg:grid-cols-1 gap-4 sm:gap-x-6 lg:pt-1">
                                        <div>
                                        <x-input type="hidden" name="id" value="{{$id}}" />
                                        <div>
                                            <label class="mb-0">
                                            {{ __('translation.Name') }} :
                                            </label>
                                            <div class="w-full">
                                                <x-input type="text" name="name" value="{{$data_agent_cargo->name}}" />
                                            </div>
                                        </div>
                                        <hr class="w-full border border-dashed border-r-0 border-b-0 border-l-0 border-blue-500 mb-5">
                                        <strong>Add Shipping Mark<strong>  <br/>

                                        <x-button type="button" style="margin-right:15%" data-cargo-id="{{$data_agent_cargo->id}}" color="green" id="__btnShippingMark">
                                            <i class='fas fa-plus'></i>
                                        </x-button>

                                        <div id="__wrapper_ShippingMarkList"></div>
                                            <div id="__wrapper_NoProduct">
                                                <div class="w-full p-0 rounded-lg text-center">
                                                    <span class="font-bold text-lg text-gray-500">
                                                    @if (empty($data_agent_cargo)) No Product Added @endif
                                                    </span>
                                                </div>
                                            </div>
                                        </div>

                            @if (isset($agent_cargo_marks))
                            @foreach ($agent_cargo_marks as $item)
                            <div id="__row_ProductItem{{$item->id}}">
                                <div class="flex flex-row mb-5 py-4 border border-solid border-t-0 border-r-0 border-l-0 border-gray-200">
                                    <div class="w-full sm:w-3/4 md:w-5/6">
                                        <div class="grid grid-cols-1 sm:grid-cols-1 md:grid-cols-1 lg:grid-cols-1 gap-4 sm:gap-x-6 lg:pt-1">

                                            <div class="md:col-span-2 lg:col-span-1">
                                                <div class="mb-4">

                                                    <div class="grid md:grid-cols-3 sm:grid-cols-1 gap-4">

                                                        <div>
                                                            <label class="mb-0">
                                                                {{ __('translation.Shipping Mark') }} :
                                                            </label>
                                                            <div class="w-full">
                                                                <x-input type="text" name="shipping_mark[]" value="{{$item->shipping_mark}}" />
                                                            </div>
                                                        </div>


                                                        <div>
                                                            <label class="mb-0">
                                                                {{ __('translation.Ship Type') }} :
                                                            </label>
                                                            <div class="w-full">
                                                            <x-select name="ship_type_id[]" class="type" style="width: 100%;">
                                                                <option value="" selected disabled>
                                                                    {{ '- ' . __('translation.Select Ship Type') . ' -' }}
                                                                </option>
                                                                @if(isset($shipTypes))
                                                                    @foreach($shipTypes as $shiptype)
                                                                    <option value="{{$shiptype->id}}" @if($shiptype->id == $item->ship_type_id) selected @endif>{{$shiptype->name}}</option>
                                                                    @endforeach
                                                                @endif
                                                            </x-select>
                                                            </div>
                                                        </div>

                                                        <div class="hidden lg:block text-right lg:text-left lg:mt-5 xl:mt-3">
                                                            <x-button type="button" color="red" class="mt-3 block lg:relative w-full lg:w-auto" data-id="{{$item->id}}" onClick="removeItem(this)">
                                                            <i class="fas fa-trash-alt"></i>
                                                            </x-button>
                                                        </div>

                                                    </div>


                                            </div>


                                            </div>

                                        </div>
                                    </div>
                                </div>
                            </div>

                            @endforeach
                        @endif


                                        </div>
                                    </div>
                                </div>
                            </div>

                            </x-card.body>

                    </div>
                    <div>

                    </div>
                </div>
                <div>
                    <x-card.card-default>
                        <x-card.header>
                            <x-card.title>
                                {{ __('translation.China Warehouse Addresses') }}
                            </x-card.title>
                            <x-button type="button" style="margin-right:15%"  color="green" id="__btnAddWarehouse">
                                <i class='fas fa-plus'></i>
                            </x-button>

                        </x-card.header>
                        <x-card.body>
                            <hr class="w-full border border-dashed border-r-0 border-b-0 border-l-0 border-blue-500 mb-5">
                            <div class="mb-0">
                                <div id="__wrapper_ProductList"></div>
                                    <div id="__wrapper_NoProduct">
                                        <div class="w-full p-0 rounded-lg text-center">
                                            <span class="font-bold text-lg text-gray-500">
                                            @if (empty($data_agent_cargo)) No Product Added @endif
                                            </span>
                                        </div>
                                    </div>
                                </div>

                            @if (isset($agent_cargo_warehouses))
                            @foreach ($agent_cargo_warehouses as $item)
                            <div class="hidden1"   id="__row_ProductItem{{$item->id}}">
                                <div class="flex flex-row mb-5 py-4 border border-solid border-t-0 border-r-0 border-l-0 border-gray-200">
                                    <div class="w-full sm:w-3/4 md:w-5/6">
                                        <div class="grid grid-cols-1 sm:grid-cols-1 md:grid-cols-1 lg:grid-cols-1 gap-4 sm:gap-x-6 lg:pt-1">

                                            <div class="md:col-span-2 lg:col-span-1">
                                                <div class="mb-4">

                                                <div class="grid md:grid-cols-3 gap-4">

                                                    <div>
                                                        <label class="mb-0">
                                                            {{ __('translation.Location') }} :
                                                        </label>
                                                        <div class="w-full">
                                                            <x-input type="text" name="location[]" value="{{$item->location}}" />
                                                        </div>
                                                    </div>


                                                    <div>
                                                        <label class="mb-0">
                                                            {{ __('translation.Address') }} :
                                                        </label>
                                                        <div class="w-full">
                                                            <x-textarea name="address[]">{{$item->address}}</x-textarea>
                                                        </div>
                                                    </div>

                                                    <div class="hidden lg:block text-right lg:text-left lg:mt-5 xl:mt-3">
                                                        <x-button type="button" color="red" class="mt-2 block lg:relative w-full lg:w-auto" data-id="{{$item->id}}" onClick="removeItem(this)">
                                                        <i class="fas fa-trash-alt"></i>
                                                        </x-button>
                                                    </div>

                                                </div>
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
            </div>
        </card>
    </x-card>
    @endif

    <div class="hidden" id="__templateShippingMarkItem">
        <div class="flex flex-row mb-5 py-4 border border-solid border-t-0 border-r-0 border-l-0 border-gray-200" id="__row_ProductItem">
            <div class="w-full sm:w-3/4 md:w-5/6">
                <div class="grid grid-cols-1 sm:grid-cols-1 md:grid-cols-1 lg:grid-cols-1 gap-4 sm:gap-x-6 lg:pt-1">

                    <div class="md:col-span-2 lg:col-span-1">
                        <div class="mb-4">

                        <div class="grid md:grid-cols-3 sm:grid-cols-1 gap-4">

                            <div>
                                <label class="mb-0">
                                    {{ __('translation.Shipping Mark') }} :
                                </label>
                                <div class="w-full">
                                    <x-input type="text" name="shipping_mark[]" value="" />
                                </div>
                            </div>


                            <div>
                                <label class="mb-0">
                                    {{ __('translation.Ship Type') }} :
                                </label>
                                <div class="w-full">
                                    <x-select name="ship_type_id[]" class="type" style="width: 100%;">
                                        <option value="" selected disabled>
                                            {{ '- ' . __('translation.Select Ship Type') . ' -' }}
                                        </option>
                                        @if(isset($shipTypes))
                                            @foreach($shipTypes as $shiptype)
                                            <option value="{{$shiptype->id}}">{{$shiptype->name}}</option>
                                            @endforeach
                                        @endif
                                    </x-select>
                                </div>
                            </div>
                            <div class="hidden lg:block text-right lg:text-left lg:mt-5 xl:mt-3">
                                <x-button type="button" color="red" class=" mt-2 block lg:relative w-full lg:w-auto"  onClick="removeItem(this)">
                                <i class="fas fa-trash-alt"></i>
                                </x-button>
                            </div>

                        </div>


                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="hidden" id="__templateProductItem">
        <div class="flex flex-row mb-5 py-4 border border-solid border-t-0 border-r-0 border-l-0 border-gray-200" id="__row_ProductItem">
            <div class="w-full sm:w-3/4 md:w-5/6">
                <div class="grid grid-cols-1 sm:grid-cols-1 md:grid-cols-1 lg:grid-cols-1 gap-4 sm:gap-x-6 lg:pt-1">

                    <div class="md:col-span-2 lg:col-span-1">
                        <div class="mb-4">

                        <div class="grid md:grid-cols-3 gap-4">

                                <div>
                                    <label class="mb-0">
                                        {{ __('translation.Location') }} :
                                    </label>
                                    <div class="w-full">
                                        <x-input type="text" name="location[]" value="" />
                                    </div>
                                </div>


                                <div>
                                    <label class="mb-0">
                                        {{ __('translation.Address') }} :
                                    </label>
                                    <div class="w-full">
                                        <x-textarea name="address[]"></x-textarea>
                                    </div>
                                </div>

                                <div class="hidden lg:block text-right lg:text-left lg:mt-5 xl:mt-3">
                                    <x-button type="button" color="red" class="mt-2 block lg:relative w-full lg:w-auto"  onClick="removeItem(this)">
                                    <i class="fas fa-trash-alt"></i>
                                    </x-button>
                                </div>

                            </div>
                        </div>


                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('bottom_js')
        <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js"></script>
         <script type="text/javascript">


            $(window).on('load', function() {
                $('#__formChinaCargo')[0].reset();
            });


            $(document).ready(function() {


                $(document).on('click', '#__btnShippingMark', function(event) {
                    event.preventDefault();
                    let cargoID = $(this).data('cargo-id');
                    renderShippingMarkToList(cargoID);
                    return false;
                });



                 $(document).on('click', '#__btnAddWarehouse', function(event) {
                    event.preventDefault();
                    let templateProductItemElement = $('#__templateProductItem').clone();
                    $('#__wrapper_ProductList').prepend(templateProductItemElement.html());

                    return false;
                });
            });
        </script>




        <script>



        const renderShippingMarkToList = ClickValue => {

            $('#__wrapper_NoProduct').hide();
            let templateShippingMarkElement = $('#__templateShippingMarkItem').clone();
            $('#__wrapper_ShippingMarkList').prepend(templateShippingMarkElement.html());
            }




            const removeItem = el => {
                const dataId = el.getAttribute('data-id');
                $(el).parent().parent().remove();
            }

            $('#__formChinaCargo').submit(function(event) {
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
                       console.log(responseJson)
                        $('html, body').animate({
                            scrollTop: 0
                        }, 500);

                        $('.alert').addClass('hidden');
                        $('#__alertSuccess').removeClass('hidden');
                        $('#__content_alertSuccess').html(null);
                        $('#__content_alertSuccess').html(responseJson.message);

                        setTimeout(() => {
                            window.location.href = '{{ route('po_settings') }}';
                        }, 1500);
                    },
                    error: function(response) {
                        let responseJson = response.responseJSON;

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





