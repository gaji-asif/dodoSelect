<x-app-layout>
    @section('title', 'Manage Seller')

    <link rel="stylesheet" href="{{ URL::asset('css/bootstrap.min.css') }}">
    <script src="{{ URL::asset('js/jquery.min.js') }}"></script>
    <script src="{{ URL::asset('js/popper.min.js') }}"></script>
    <script src="{{ URL::asset('js/bootstrap.min.js') }}"></script>

    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ URL::asset('css/jquery.dataTables.min.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="{{ URL::asset('css/dataTables.bootstrap4.min.css') }}" rel="stylesheet">

    <script src="{{ URL::asset('js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ URL::asset('js/dataTables.bootstrap4.min.js') }}"></script>
    <!-- Fonts -->

    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap">

    <!-- Styles -->
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <!-- Scripts -->
    <script src="{{ asset('js/app.js') }}" defer></script>


    <style>
        [x-cloak] {
            display: none;
        }

        .duration-300 {
            transition-duration: 300ms;
        }

        .ease-in {
            transition-timing-function: cubic-bezier(0.4, 0, 1, 1);
        }

        .ease-out {
            transition-timing-function: cubic-bezier(0, 0, 0.2, 1);
        }

        .scale-90 {
            transform: scale(.9);
        }

        .scale-100 {
            transform: scale(1);
        }

        .modal-hide {
            display: none !important;
        }

        .cutome_image {
            height: 70px;
            width: 70px
        }

        .custome_quantity {
        | color: inherit;
            text-decoration: underline;
        }

        div.dataTables_wrapper div.dataTables_filter input {
            border: 1px solid gray;
            height: 40px;
        }

        table.dataTable tbody tr {
            text-align: center;
        }

        .hide {
            display: none;
        }

    </style>
    {{-- <a href="{{asset('qrcode.svg')}}" download><img src="{{asset('qrcode.svg')}}"  alt=""></a> --}}
    <x-card title="Product ({{count($products)}})">
        <div class="mt-6">
            @if(session('success'))
                <x-alert-success>{{ session('success') }}</x-alert-success>
            @endif
            @if(session('danger'))
                <x-alert-danger>{{ session('danger') }}</x-alert-danger>
            @endif
            @if ($errors->any())
                <x-alert-danger>
                    <ul class="mt-3 list-disc list-inside text-sm text-red-600">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </x-alert-danger>
            @endif


            <x-button class="mb-6" color="green" data-toggle="modal" data-target="#SyncModalProduct"
                      id="BtnSyncModalProduct">
                <p class="mr-1">Sync Product</p>
                <i class="fas fa-sync"></i>
            </x-button>


            <x-button class="mb-6" color="green" id="BtnInsert">
                <p class="mr-1">Add Product</p>
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="white" width="18px" height="18px">
                    <path d="M0 0h24v24H0z" fill="none"/>
                    <path
                        d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm5 11h-4v4h-2v-4H7v-2h4V7h2v4h4v2z"/>
                </svg>
            </x-button>
            <x-button color="green" id="BtnImport">
                <p class="mr-1">Import</p>
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="white" width="18px" height="18px">
                    <path d="M0 0h24v24H0z" fill="none"/>
                    <path d="M9 16h6v-6h4l-7-7-7 7h4zm-4 2h14v2H5z"/>
                </svg>
            </x-button>


            <div class="modal-import modal-hide">
                <div style="background-color: rgba(0,0,0,0.5)"
                     class="overflow-auto fixed inset-0 z-10 flex items-center justify-center">
                    <div class="bg-white w-11/12 md:max-w-md mx-auto rounded shadow-lg pt-4 pb-6 text-left px-6"
                         x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 scale-90"
                         x-transition:enter-end="opacity-100 scale-100">

                        <div class="flex justify-between items-center pb-3">
                            <p class="text-2xl font-bold">Import Product</p>
                            {{-- tombol close --}}


                            <div class="cursor-pointer z-50" id="closeModalImport">
                                <svg class="fill-current text-black" xmlns="http://www.w3.org/2000/svg" width="18"
                                     height="18" viewBox="0 0 18 18">
                                    <path
                                        d="M14.53 4.53l-1.06-1.06L9 7.94 4.53 3.47 3.47 4.53 7.94 9l-4.47 4.47 1.06 1.06L9 10.06l4.47 4.47 1.06-1.06L10.06 9z">
                                    </path>
                                </svg>
                            </div>
                        </div>
                        <p class="pb-3" style="margin-bottom: 20px;">Download Sample Format File
                            <a target="_blank" style="font-weight: bold; text-decoration: underline; margin-left: 10px;"
                               href="{{asset('/download-format.xlsx')}}">
                                <span class="fa fa-fw fa-download"></span>
                                Download</a></p>
                        <form method="POST" action="{{ route('product_bulk_import') }}" id="form-import"
                              enctype="multipart/form-data">
                            <div>
                                @csrf
                                <x-label>
                                    File
                                </x-label>
                                <x-input type="file" name="file" id="file" required>
                                </x-input>
                            </div>

                            <div class="flex justify-end mt-4">
                                <x-button color="blue" type="submit" id="BtnImportSubmit">Save</x-button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- insert modal -->
            <div class="modal-insert modal-hide">
                <div style="background-color: rgba(0,0,0,0.5)"
                     class="overflow-auto fixed inset-0 z-10 flex items-center justify-center">
                    <div class="bg-white w-11/12 md:max-w-md mx-auto rounded shadow-lg pt-4 pb-6 text-left px-6"
                         x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 scale-90"
                         x-transition:enter-end="opacity-100 scale-100">

                        <div class="flex justify-between items-center pb-3">
                            <p class="text-2xl font-bold">Add Product</p>
                            {{-- tombol close --}}
                            <div class="cursor-pointer z-50" id="closeModalInsert">
                                <svg class="fill-current text-black" xmlns="http://www.w3.org/2000/svg" width="18"
                                     height="18" viewBox="0 0 18 18">
                                    <path
                                        d="M14.53 4.53l-1.06-1.06L9 7.94 4.53 3.47 3.47 4.53 7.94 9l-4.47 4.47 1.06 1.06L9 10.06l4.47 4.47 1.06-1.06L10.06 9z">
                                    </path>
                                </svg>
                            </div>
                        </div>

                        <form method="POST" action="{{ route('insert product') }}" id="form-insert"
                              enctype="multipart/form-data">
                            @csrf
                            <div class="mt-6" style="width: 49%; float: left; margin-right: 1%;">
                                <x-label>
                                    Product Name
                                </x-label>
                                <x-input type="text" name="product_name" id="product_name" :value="old('product_name')"
                                         required>
                                </x-input>
                            </div>
                            <div class="mt-6" style="width: 49%; float: left;">
                                <x-label>
                                    Product Code
                                </x-label>
                                <x-input type="text" name="product_code" id="product_code" :value="old('product_code')"
                                         required>
                                </x-input>
                            </div>
                            <div class="mt-6" style="width: 49%; float: left; margin-right: 1%;">
                                <x-label>
                                    Default Price
                                </x-label>
                                <x-input type="text" name="price" id="price" :value="old('price')" required>
                                </x-input>
                            </div>
                            <div class="mt-6" style="width: 49%; float: left; margin-bottom: 20px;">
                                <x-label>
                                    Weight
                                </x-label>
                                <x-input type="text" name="weight" id="weight" :value="old('weight')" required>
                                </x-input>
                            </div>


                            <div class="increment">

                                <div class="mt-6" style="width: 49%; float: left;margin-bottom:20px">
                                    <x-label>
                                        Price
                                    </x-label>
                                    <x-input type="text" name="shop_price[]" id="shop_price" :value="old('shop_price')"
                                             required>
                                    </x-input>
                                </div>
                                <div class="float-right">
                                    <button
                                        class="btn btn-success bg-green-500 text-white rounded px-2 py-1 mr-1 capitalize cursor-pointer"
                                        type="button"><i class="fa fa-plus"></i></button>
                                </div>
                            </div>


                            <div class="hide clone">
                                <div class=" new">

                                    <div class="mt-6" style="width: 49%; float: left;margin-bottom:20px">
                                        <x-label>
                                            Price
                                        </x-label>
                                        <x-input type="text" name="shop_price[]" id="shop_price"
                                                 :value="old('shop_price')">
                                        </x-input>
                                    </div>
                                    <div class="float-right">
                                        <button
                                            class="btn btn-danger bg-red-500 text-white rounded px-2 py-1 mr-1 capitalize cursor-pointer"
                                            type="button"><i class="fa fa-times"></i></button>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-6">
                                <x-label>
                                    Pieces / Pack
                                </x-label>
                                <x-input type="text" name="pack" id="pack" :value="old('pack')" required>
                                </x-input>
                            </div>

                            <div class="mt-6">
                                <x-label>
                                    Specifications
                                </x-label>
                                <textarea name="specifications" id="specifications"
                                          class="border-radius border-gray-300" cols="45"
                                          rows="2">@if(isset($data->details)) {{$data->details}}@else{{old('specifications')}}@endif</textarea>
                            </div>
                            <div class="mt-6">
                                <x-label>
                                    Image
                                </x-label>
                                <x-input type="file" name="image" id="image" :value="old('image')" required>
                                </x-input>
                            </div>

                            <div class="flex justify-end mt-4">
                                <x-button type="submit" color="blue">Save</x-button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>


            {{-- update modal --}}
            <div class="modal-update modal-hide">
                <div style="background-color: rgba(0,0,0,0.5)"
                     class="overflow-auto fixed  inset-0 z-10 flex items-center justify-center">
                    <div
                        class="bg-white w-11/12 md:max-w-md overflow-y-auto mx-auto rounded shadow-lg pt-4 pb-6 text-left px-6"
                        x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 scale-90"
                        x-transition:enter-end="opacity-100 scale-100">

                        <div class="flex justify-between items-center pb-3">
                            <p class="text-2xl font-bold">Update Product</p>
                            {{-- tombol close --}}
                            <div class="cursor-pointer z-50" id="closeModalUpdate">
                                <svg class="fill-current text-black" xmlns="http://www.w3.org/2000/svg" width="18"
                                     height="18" viewBox="0 0 18 18">
                                    <path
                                        d="M14.53 4.53l-1.06-1.06L9 7.94 4.53 3.47 3.47 4.53 7.94 9l-4.47 4.47 1.06 1.06L9 10.06l4.47 4.47 1.06-1.06L10.06 9z">
                                    </path>
                                </svg>
                            </div>
                        </div>

                        <form style="max-height:90vh" method="POST" action="{{ route('wc_product_update') }} "
                              id="form-update" enctype="multipart/form-data"></form>
                    </div>
                </div>
            </div>
            <div class="modal-producut modal-hide">
                <div style="background-color: rgba(0,0,0,0.5)"
                     class="overflow-auto fixed  inset-0 z-10 flex items-center justify-center">
                    <div
                        class="bg-white w-11/12 md:max-w-md overflow-y-auto mx-auto rounded shadow-lg pt-4 pb-6 text-left px-6"
                        x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 scale-90"
                        x-transition:enter-end="opacity-100 scale-100">

                        <div style="max-height:90vh" id="form-producut"></div>
                    </div>
                </div>
            </div>
            {{-- Quantity modal --}}
            <div class="modal-quantity modal-hide">
                <div style="background-color: rgba(0,0,0,0.5)"
                     class="overflow-auto fixed  inset-0 z-10 flex items-center justify-center">
                    <div
                        class="bg-white w-11/12 md:max-w-md overflow-y-auto mx-auto rounded shadow-lg pt-4 pb-6 text-left px-6"
                        x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 scale-90"
                        x-transition:enter-end="opacity-100 scale-100">

                        <div class="flex justify-between items-center pb-3">
                            <p class="text-2xl font-bold">Update Quantity</p>
                            {{-- tombol close --}}
                            <div class="cursor-pointer z-50" id="closeModalQuantity">
                                <svg class="fill-current text-black" xmlns="http://www.w3.org/2000/svg" width="18"
                                     height="18" viewBox="0 0 18 18">
                                    <path
                                        d="M14.53 4.53l-1.06-1.06L9 7.94 4.53 3.47 3.47 4.53 7.94 9l-4.47 4.47 1.06 1.06L9 10.06l4.47 4.47 1.06-1.06L10.06 9z">
                                    </path>
                                </svg>
                            </div>
                        </div>

                        <form style="max-height:90vh" method="POST" action="{{ route('seller quantity update') }} "
                              id="form-quantity" enctype="multipart/form-data"></form>
                    </div>
                </div>
            </div>
        </div>
    <!--  <form method="POST" action="{{route('print qr code')}}" id="form-import " class="new_form" enctype="multipart/form-data">
                @csrf

        <div class="flex justify-end mt-4">
            <button type="submit" id="click-submit" class="bg-blue-500 text-white rounded px-2 py-1 mr-1 capitalize cursor-pointer mb-2">Print</button>
        </div>
    </form> -->


        <div class="modal fade" id="SyncModalProduct" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
             aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel"><strong>Sync Products</strong></h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="col-lg-12">
                            <div class="form-group">
                                <label for="exampleInputEmail1">Shop</label>

                                <?php //echo "<pre>"; print_r($shops); echo "</pre>"; ?>
                                <select id="shop" style="width: 100%; margin-bottom: 15px;"
                                        class="form-control block font-medium w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 mt-1 js-example-basic-single3"
                                        name="shop_id" required>
                                    <option></option>
                                    @if (isset($shops))
                                        @foreach ($shops as $shop)
                                            <option data-site_url="{{$shop->site_url}}"
                                                    data-key="{{$shop->rest_api_key}}"
                                                    data-secrete="{{$shop->rest_api_secrete}}"
                                                    value="{{$shop->id}}">{{$shop->name}}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                        </div>

                        <div class="col-lg-12">
                            <div class="form-group">
                                <label for="exampleInputEmail1">Sync Record Total</label>
                                <input
                                    class="form-control block font-medium w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 mt-1"
                                    id="number_of_products" name="number_of_products" placeholder="Enter -1 for ALL"
                                    type="text"/>

                            </div>
                        </div>


                        <div class="col-lg-12 message_sync">

                        </div>

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn bg-green-500 text-white" data-dismiss="modal">Close</button>
                        <button id="btn_sync_product" type="submit" class="btn btn-success">Load</button>

                    </div>
                </div>
            </div>
        </div>

        <div class="mt-5" id="datatable_wrapper">

        <?php //echo "<pre>"; print_r($products); echo "<pre>"; ?>
        <!-- Datatable -->
            <table class="table-auto border-collapse w-full border mt-4" id="datatable">
                <thead class="border bg-green-300">
                <tr class="rounded-lg text-sm font-medium text-gray-700 text-left">
                    <th class="dt-checkboxes-cell dt-checkboxes-select-all sorting_disabled" tabindex="0"
                        aria-controls="datatable" rowspan="1" colspan="1" style="width: 17px;" data-col="0"
                        aria-label=""><input type="checkbox" autocomplete="off"></th>
                    <th class="px-4 py-2 border-2 text-center">Id</th>
                    <th class="px-4 py-2 border-2 text-center">Images</th>
                    <th class="px-4 py-2 border-2 text-center">Product Name</th>
                    <th class="px-4 py-2 border-2 text-center">Product Code</th>
                    <th class="px-4 py-2 border-2 text-center">Quantity</th>
                    <th class="px-4 py-2 border-2 text-center">Incoming</th>
                    <th class="px-4 py-2 border-2 text-center">Prices/Pack</th>
                    <th class="px-4 py-2 border-2 text-center">Weight</th>
                    <th class="px-4 py-2 border-2 text-center">Website</th>
                    <th class="px-4 py-2 border-2 text-center">Managw</th>
                </tr>
                </thead>
                <tbody>


                </tbody>
            </table>

        </div>


        <link type="text/css" href="{{ URL::asset('css/dataTables.checkboxes.css') }}" rel="stylesheet"/>
        <script type="text/javascript" src="{{ URL::asset('js/dataTables.checkboxes.min.js') }}"></script>


        <script>

            $(document).ready(function () {

                dataTables("{{ route('wc_products') }}");
                var datatable;

                function dataTables(url) {
                    // Datatable
                    datatable = $('#datatable').DataTable({
                        processing: true,
                        serverSide: true,
                        columnDefs: [
                            {
                                'targets': 0,
                                'checkboxes': {
                                    'selectRow': true
                                }
                            },
                            {'visible': false, 'targets': 6}
                        ],
                        // select : {
                        //     style: 'multi'
                        // },
                        order: [[3, 'desc']],
                        ajax: url,
                        columns: [

                            {
                                name: 'checkbox',
                                data: 'checkbox'
                            },
                            {
                                name: 'id',
                                data: 'product_id'
                            },
                            {
                                name: 'website_id',
                                data: 'website_id'
                            },
                            {
                                name: 'created_at',
                                data: 'created_at'
                            },
                            {
                                name: 'quantity',
                                data: 'quantity'
                            },
                            // {
                            //     name: 'qrCode',
                            //     data: 'qrCode'
                            // },

                            {
                                name: 'price',
                                data: 'price'
                            },

                            {
                                name: 'price',
                                data: 'price'
                            },
                            {
                                name: 'price',
                                data: 'price'
                            },
                            {
                                name: 'price',
                                data: 'price'
                            },
                            {
                                name: 'status',
                                data: 'status'
                            },
                            {
                                name: 'manage',
                                data: 'manage'
                            }
                        ]
                    });
                }
            });
        </script>


        <script type="text/javascript">
            $(document).ready(function () {
                $('#btn_sync_product').click(function () {
                    var website_id = $('#SyncModalProduct #shop option:selected').val();

                    var number_of_products = $('input[name="number_of_products"]').val();

                    var site_url = $('#SyncModalProduct #shop option:selected').attr('data-site_url');
                    var consumer_key = $('#SyncModalProduct #shop option:selected').attr('data-key');
                    var consumer_secret = $('#SyncModalProduct #shop option:selected').attr('data-secrete');
                    if (typeof consumer_key === "undefined") {

                        $('.message_sync').html('<div class="alert alert-danger" role="alert">Please Select A Shop</div>');
                        return;
                    }
                    if (consumer_key === "") {
                        $('.message_sync').html('<div class="alert alert-danger" role="alert">Please add REST API Consumer Key</div>');
                        return;
                    }

                    if (consumer_secret === "") {
                        $('.message_sync').html('<div class="alert alert-danger" role="alert">Please add REST API Consumer Secrete</div>');
                        return;
                    }

                    $(".page-item").removeClass("active");
                    $(this).parents("li").addClass("active");
                    $('.message_sync').html('<div class="alert alert-warning" role="alert">Proccesing...Please wait a bit </div>');
                    $.ajax({
                        url: '{{ route('wc_products_sync') }}',
                        type: 'post',
                        data: {
                            'number_of_products': number_of_products,
                            'website_id': website_id,
                            'site_url': site_url,
                            'consumer_key': consumer_key,
                            'consumer_secret': consumer_secret,
                            'page': 1,
                            'limit': 100,
                            'per_page': 100,
                            '_token': $('meta[name=csrf-token]').attr('content')
                        },
                        success: function (data) {
                            //$('tbody').html(data);
                            $('.message_sync').html('<div class="alert alert-success" role="alert">Products are Synchronized successfully...</div>');
                            location.reload();
                        }
                    });
                });
            });

        </script>


        <script>
            $(document).ready(function () {
                $('#records-limit').change(function () {
                    // $('form').submit();
                })
            });
        </script>


        <script type="text/javascript">
            $(document).ready(function () {
                $('.page-link').click(function () {
                    var page = $(this).attr("data-index");
                    $('tbody').html("<tr><th colspan=11>Proccesing...</th></tr>");
                    $.ajax({
                        url: '{{ route('wc_products pagination') }}',
                        type: 'post',
                        data: {
                            'page': page,
                            '_token': $('meta[name=csrf-token]').attr('content')
                        },
                        success: function (data) {
                            console.log(data);
                            $('tbody').html(data);


                        }
                    });
                });

            });
        </script>


    </x-card>

    <link type="text/css"
          href="https://gyrocode.github.io/jquery-datatables-checkboxes/1.2.12/css/dataTables.checkboxes.css"
          rel="stylesheet"/>
    <script type="text/javascript"
            src="https://gyrocode.github.io/jquery-datatables-checkboxes/1.2.12/js/dataTables.checkboxes.min.js"></script>
    <script>


        $(document).ready(function () {
            console.log($(this).val());
            //dataTables("{{ route('wc_data_product') }}?date=" + $(this).val());
            var datatable;

            $('#inputDate').change(function () {
                datatable.destroy();
                // console.log($(this).val());
                //  dataTables("{{ route('wc_data_product') }}?date=" + $(this).val());
            });


            $('#BtnInsert').click(function () {
                $('.modal-insert').removeClass('modal-hide');
            });

            $(document).on('click', '#BtnUpdate', function () {
                $('.modal-update').removeClass('modal-hide');
                $.ajax({
                    url: '{{ route('wc_data_product') }}?id=' + $(this).data('id'),
                    beforeSend: function () {
                        $('#form-update').html('Loading....');
                    }
                }).done(function (result) {
                    $('#form-update').html(result);
                });
            });

            $(document).on('click', '#closeModalUpdate', function () {
                $('.modal-update').addClass('modal-hide');
            });

            $('#closeModalInsert').click(function () {
                $('.modal-insert').addClass('modal-hide');
            });

            $(document).on('click', '#BtnDelete', function () {
                let drop = confirm('Are you sure?');
                if (drop) {
                    var id = $(this).data('id');
                    $("#tr_" + id).addClass("current2");

                    $.ajax({
                        url: '{{ route('wc_product_delete') }}',
                        type: 'post',
                        data: {
                            'id': $(this).data('id'),
                            '_token': $('meta[name=csrf-token]').attr('content')
                        },
                        beforeSend: function () {
                            // Pesan yang muncul ketika memproses delete
                        }
                    }).done(function (result) {
                        if (result.status === 1) {
                            // Pesan jika data berhasil di hapus
                            alert('Data deleted successfully');
                            $("#tr_" + id).hide();
                        } else {

                            alert(result.message);

                        }
                    });
                }
            });

            $(document).on('click', '#BtnQuantity', function () {
                $('.modal-quantity').removeClass('modal-hide');
                $.ajax({
                    url: '{{ route('product data seller') }}?id=' + $(this).data('id'),
                    beforeSend: function () {
                        $('#form-quantity').html('Loading');
                    }
                }).done(function (result) {
                    $('#form-quantity').html(result);
                });
            });

            $(document).on('click', '#closeModalQuantity', function () {
                $('.modal-quantity').addClass('modal-hide');
            });

            $('.new_form').on('submit', function (e) {

                var form = this;

                var rows_selected = datatable.column(0).checkboxes.selected();

                $.each(rows_selected, function (index, rowId) {
                    // Create a hidden element
                    $(form).append(
                        $('<input>')
                            .attr('type', 'hidden')
                            .attr('name', 'product_code[]')
                            .val(rowId)
                    );
                });
            });

        });

    </script>
    <script type="text/javascript">

        $('#BtnImport').click(function () {
            $('.modal-import').removeClass('modal-hide');
        });
        $('#closeModalImport').click(function () {
            $('.modal-import').addClass('modal-hide');
        });

        $(document).on('click', '#BtnProduct', function () {
            $('.modal-producut').removeClass('modal-hide');
            $.ajax({
                //  url: '{{ route('data purchase order') }}?id=' + $(this).data('id'),
                beforeSend: function () {
                    $('#form-producut').html('Loading');
                }
            }).done(function (result) {
                $('#form-producut').html(result);
            });
        });

        $(document).on('click', '#closeModalproduct', function () {
            $('.modal-producut').addClass('modal-hide');
        });


    </script>
</x-app-layout>
<script>
    $(document).ready(function () {
        $(".btn-success").click(function () {

            var html = $(".hide").html();
            // console.log(html);
            $(".increment").after(html);
        });

        $("body").on("click", ".btn-danger", function () {
            $(this).parents(".new").remove();
        });

    });
</script>
