<x-app-layout>

    @section('title')
        {{ __('translation.Purchase Order') }}
    @endsection

    @push('bottom_css')
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/jquery-datatables-checkboxes@1.2.12/css/dataTables.checkboxes.css">

        <style type="text/css">
            .card-header h2 {
                font-size: 22px;
                font-weight: bold !important;
            }

            .lead {
                font-size: 18px;
            }

            #BtnSyncModalOrder {
                float: right;
            }

            .sync_selected li {
                cursor: pointer;
            }

            .filter_status {
                list-style: none;
                margin: 8px 0 0;
                padding: 0;
                font-size: 13px;
                float: left;
                color: #646970;
                width: 100%;
            }

            .filter_status li {
                display: inline-block;
                margin: 0;
                white-space: pre-wrap;
                cursor: pointer;
                border-radius: 5px;
                padding: 5px 10px;
            }

            .filter_status li:hover {
                font-weight: bold;
            }

            .filter_status li.active {
                font-weight: bold;
            }

            .btn_processing {
                background: #3c3ce2;
                color: #fff;
            }

            .btn_ready_to_ship {
                background: #ffa500;
                color: #fff;
            }

            .btn_process_order {
                background: #6d6deb;
                color: #fff;
            }

            .address p {
                margin: 0 !important;
                padding: 0 !important;
            }

            .loader {
                position: fixed;
                left: 0px;
                top: 0px;
                width: 100%;
                height: 100%;
                z-index: 9999;

            }
        </style>
    @endpush


    @if(\App\Models\Role::checkRolePermissions('Can access menu: WooCommerce - Order'))
        <div class="col-span-12">
            <div class="card">
                <div class="card-body">
                    <div class="card-title">
                        <h4><strong>Purchase Order</strong></h4>
                    </div>

                    @if(session()->has('error'))
                        <div class="alert alert-danger mb-3 background-danger" role="alert">
                            {{ session()->get('error') }}
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    @endif

                    @if(session()->has('success'))
                        <div class="alert alert-success mb-3 background-success" role="alert">
                            {{ session()->get('success') }}
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    @endif

                    <div id="messageStatus"></div>

                    <?php
                    $btn['processing'] = 0;
                    $btn['ready-to-ship'] = 0;
                    $numItems = count($group_status);
                    $i = 0;
                    foreach ($group_status as $item) :
                        $status = $item->status;
                        $right_bar = "|";
                        if (++$i === $numItems - 2) {
                            $right_bar = "";
                        }

                        $btn[$status] = $item->total;
                    endforeach;
                    ?>

                    <x-button data-status='processing' class="mb-6 btn_processing" color="green" id="BtnInsert">
                        <p class="mr-1">Processing (<?php echo $btn['processing']; ?>)</p>
                    </x-button>

                    <x-button class="mb-6 btn_ready_to_ship" color="green" id="BtnInsert">
                        <p class="mr-1">Ready to Ship (<?php echo $btn['ready-to-ship']; ?>)</p>
                    </x-button>

                    <x-button class="mb-6" color="green" data-toggle="modal" data-target="#SyncModalOrder" id="BtnSyncModalOrder">
                        <p class="mr-1">Sync Order</p>
                        <i class="fas fa-sync"></i>
                    </x-button>

                    <!--   <x-button hidden class="mb-6" color="green"  data-toggle="modal" data-target="#SyncModalCountriesState"  id="BtnInsert">
                        <p class="mr-1">Sync Countires/State</p>
                        <i class="fas fa-sync"></i>
                    </x-button> -->

                    <div class='row hide'>
                        <div class='col-md-12'>
                            <x-button data-status='processing' class="mb-6 btn_process_order hide" color="green" id="BtnInsert">
                                <p class="mr-1">Process orders</p>
                            </x-button>
                        </div>
                    </div>

                    <div class="modal fade" id="SyncModalOrder" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="exampleModalLabel"><strong>Sync Purchase Order</strong></h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <div class="col-lg-12">
                                        <div class="form-group">
                                            <label for="exampleInputEmail1">Shop</label>
                                            <?php //echo "<pre>"; print_r($shops); echo "</pre>";
                                            ?>
                                            <select id="shop" style="width: 100%; margin-bottom: 15px;" class="form-control block font-medium w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 mt-1 js-example-basic-single3" name="shop_id" required>
                                                <option></option>
                                                @if (isset($shops))
                                                    @foreach ($shops as $shop)
                                                        <option data-site_url="{{$shop->site_url}}" data-key="{{$shop->rest_api_key}}" data-secrete="{{$shop->rest_api_secrete}}" value="{{$shop->id}}">{{$shop->name}}</option>
                                                    @endforeach
                                                @endif
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-lg-12">
                                        <div class="form-group">
                                            <label for="exampleInputEmail1">Sync Record Total</label>
                                            <input class="form-control block font-medium w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 mt-1" id="number_of_orders" name="number_of_orders" placeholder="Enter -1 for ALL" type="text" />
                                        </div>
                                    </div>
                                    <div class="col-lg-12 message_sync"></div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn bg-green-500 text-white" data-dismiss="modal">Close</button>
                                    <button id="btn_sync_order" type="submit" class="btn btn-success">Load</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal fade" id="SyncModalCountriesState" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="exampleModalLabel"><strong>Sync Country/State</strong></h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <div class="col-lg-12">
                                        <div class="form-group">
                                            <label for="exampleInputEmail1">Shop</label>
                                            <select id="shop" style="width: 100%; margin-bottom: 15px;" class="form-control block font-medium w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 mt-1 js-example-basic-single3" name="shop_id" required>
                                                <option></option>
                                                @if (isset($shops))
                                                    @foreach ($shops as $shop)
                                                        <option data-site_url="{{$shop->site_url}}" data-key="{{$shop->rest_api_key}}" data-secrete="{{$shop->rest_api_secrete}}" value="{{$shop->id}}">{{$shop->name}}</option>
                                                    @endforeach
                                                @endif
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-lg-12 message_sync_country_state"></div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn bg-green-500 text-white" data-dismiss="modal">Close</button>
                                    <button id="btn_sync_countries_state" type="submit" class="btn btn-success">Load</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <?php
                    $arr_country = array();
                    foreach ($countries as $country) {
                        $arr_country[$country->code] = $country->name;
                    }
                    $arr_state = array();
                    foreach ($states as $state) {
                        $arr_state[$state->code] = $state->name;
                    }
                    //echo "<pre>"; print_r($total_records); echo "</pre>";
                    // echo "<pre>"; print_r($group_status); echo "</pre>";
                    ?>

                    <div class="row">
                        <div class="col-md-2">
                            <table class="table-auto border-collapse w-full2 mb-4">
                                <thead class="">
                                <tr class="rounded-lg text-sm font-medium text-gray-700 text-left">
                                    <th>
                                        <select id="bulk_action" name="bulk_action" class="custom-select custom-select-sm form-control form-control-sm">
                                            <option value="-1">Change Status</option>
                                            <option value="processing">Change status to processing</option>
                                            <option value="on-hold">Change status to On Hold</option>
                                            <option value="completed">Change status to completed</option>
                                            <option value="ready-to-ship">Change status to Ready to Ship</option>
                                        </select>
                                    </th>
                                </tr>
                            </table>
                        </div>

                        <div class="col-md-3">
                            <ul class="sync_selected">
                                <li class='inline-flex items-center px-4 py-2 bg-green-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-600 active:bg-green-600 focus:outline-none focus:border-green-600 focus:ring ring-green-300 disabled:opacity-25 transition ease-in-out duration-150 mb-6'>Sync Selected </li>
                            </ul>
                        </div>


                        <div class="col-md-7">
                            <?php //echo "<pre>"; print_r($group_status); echo "</pre>";
                            ?>
                            <ul class="filter_status">
                                <li class="all" data-status="all">All <span class="count">(<?php echo $total_records; ?>)</span> |</li>
                                <?php
                                $numItems = count($group_status);
                                $i = 0;
                                foreach ($group_status as $item) :
                                    $status = ucfirst(str_replace("-", " ", $item->status));
                                    $right_bar = "|";
                                    if (++$i === $numItems - 2) {
                                        $right_bar = "";
                                    }
                                    if ($item->status == 'processing') { } elseif ($item->status == 'ready-to-ship') { } else {
                                        echo '<li class="' . $item->status . '"  data-status="' . $item->status . '">' . $status . ' <span class="count">(' . $item->total . ')</span> ' . $right_bar . ' </li>';
                                    }
                                endforeach;
                                ?>
                            </ul>
                        </div>
                    </div>

                    <div class="w-full overflow-x-auto">
                        <table class="table-auto border-collapse w-full border mt-4" id="datatable">
                            <thead class="border bg-green-300">
                            <tr class="rounded-lg text-sm font-medium text-gray-700 text-left">
                                <th> </th>
                                <th class="px-4 py-2 border-2 text-center">Id</th>
                                <th class="px-4 py-2 border-2 text-center">Website</th>
                                <th class="px-4 py-2 border-2 text-center">Order Date</th>
                                <th class="px-4 py-2 border-2 text-center">Customer Name</th>
                                <th class="px-4 py-2 border-2 text-center">Shipping Method</th>
                                <th class="px-4 py-2 border-2 ">Labels Printed</th>
                                <th class="px-4 py-2 border-2 text-center">Payment Method</th>
                                <th class="px-4 py-2 border-2 text-center">Total</th>
                                <th class="px-4 py-2 border-2 text-center">Website Staus</th>
                                <th class="px-5 py-2 border-2 text-center">Manage</th>
                            </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>

                </div>
            </div>
        </div>
    @endif

    <div class="modal-producut modal-hide">
        <div style="background-color: rgba(0,0,0,0.5)" class="overflow-auto fixed  inset-0 z-10 flex items-center justify-center">
            <div class="bg-white w-11/12 md:max-w-md overflow-y-auto mx-auto rounded shadow-lg pt-4 pb-6 text-left px-6" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 scale-90" x-transition:enter-end="opacity-100 scale-100">
                <div style="max-height:90vh" id="form-producut"></div>
            </div>
        </div>
    </div>

    <div class="modal-message modal-hide">
        <div style="background-color: rgba(0,0,0,0.5)" class="overflow-auto fixed  inset-0 z-10 flex items-center justify-center">
            <div class="bg-white w-11/12 md:max-w-md overflow-y-auto mx-auto rounded shadow-lg pt-4 pb-6 text-left px-6" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 scale-90" x-transition:enter-end="opacity-100 scale-100">
                <div class="flex justify-between items-center pb-3">
                    <p class="text-2xl font-bold"></p>
                    <div class="cursor-pointer z-50" id="closeModalMessage">
                        <svg class="fill-current text-black" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 18 18">
                            <path d="M14.53 4.53l-1.06-1.06L9 7.94 4.53 3.47 3.47 4.53 7.94 9l-4.47 4.47 1.06 1.06L9 10.06l4.47 4.47 1.06-1.06L10.06 9z"></path>
                        </svg>
                    </div>
                </div>
                <div style="max-height:90vh" id="form-message"></div>
            </div>
        </div>
    </div>




    <div class="modal" tabindex="-1" role="dialog" id="print_level_modal">
        <div class="modal-dialog" role="document">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title">
                        <strong>Create Print Label</strong>
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="mb-4">
                        <button type="button" class="btn btn-success" id="customers_details_btn">Customer details</button>
                        <button type="button" class="btn btn-warning" id="order_details_btn">Order details</button>
                    </div>
                    <div id="printableArea">
                        <h6 class="order_shipment_color">
                            <strong id="order_id_div"></strong>
                            <strong style="float: right;" id="shipment_id_div"></strong>
                        </h6>
                        <div class="" id="order_details"></div>
                    </div>
                    <div class="mt-4 text-center">
                        <!-- <input class="btn btn-success" style="margin: 0 auto; padding: 5px 10px;" type="button" onclick="printDiv('printableArea')" value="Print" /> -->

                        <form method="POST" action="{{route('WCprintLabelPrint')}}" enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" id="shipment_id_input_val" name="shipment_id_input_val">
                            <input type="hidden" id="order_id_input_val" name="order_id_input_val">
                            <input type="hidden" id="shop_id_input_val" name="shop_id_input_val">

                            <input class="btn btn-success" type="submit" style="margin: 0 auto; padding: 5px 10px;" value="Print" />

                        </form>
                    </div>
                </div>
                <div class="modal-footer">

                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>

            </div>
        </div>
    </div>


    <div class="modal" tabindex="-1" role="dialog" id="pack_order_modal">
        <div class="modal-dialog" role="document">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title">
                        <strong>Create Order Packed </strong>
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">

                    <div id="printableArea_pack">
                        <h6 class="order_shipment_color">
                            <strong id="order_id_div_pack"></strong>
                            <strong style="float: right;" id="shipment_id_div_pack"></strong>
                        </h6>
                        <div class="" id="order_details_pack"></div>

                        <div class="mt-4 text-center">

                            <input class="btn btn-success" style="margin: 0 auto; padding: 5px 10px;" type="button" onclick="confirmPacking()" value="Confirm Packing" />


                            <form method="POST" action="{{route('WCprintLabelPrint')}}" enctype="multipart/form-data">
                                @csrf
                                <input type="hidden" id="shipment_id_input_val_pack" name="shipment_id_input_val_pack">
                                <input type="hidden" id="order_id_input_val_pack" name="order_id_input_val_pack">

                            </form>
                        </div>
                    </div>
                    <div class="modal-footer">

                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    </div>

                </div>
            </div>
        </div>
    </div>


    @push('bottom_js')
        <script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/jquery-datatables-checkboxes@1.2.12/js/dataTables.checkboxes.min.js"></script>

        <script>
            $(document).ready(function() {

                dataTables("{{ route('data order') }}");
                var datatable;

                $('.btn_processing').click(function() {
                    datatable.destroy();
                    var status = 'processing';
                    dataTables("{{ route('data order') }}?status=" + status);
                    $(".btn_process_order").removeClass('hide');
                });

                $(document).on('click', '.btn_process_order', function() {
                    var rows_selected = datatable.column(0).checkboxes.selected();
                    var total_rows = rows_selected.length;

                    var arr = [];
                    $.each(rows_selected, function(index, rowId) {
                        arr[index] = rowId;
                        datatable.cell(index, 8).data(status);
                    });

                    if (arr.length === 0) {
                        alert("Please Select At Least 1 Row...");
                        return;
                    }

                    let drop = confirm('You will be processing ' + total_rows + ' orders. Are you sure?');
                    if (drop) {
                        var jSonData = JSON.stringify(arr);
                        var status = 'ready-to-ship';
                        $.ajax({
                            url: '{{ route('data bulkStatus') }}',
                            type: "POST",
                            data: {
                                'jSonData': jSonData,
                                'status': status,
                                '_token': $('meta[name=csrf-token]').attr('content')
                            },
                            beforeSend: function() {
                                //$('#messageStatus').html("Please wait...");
                            }
                        }).done(function(result) {
                            $('#messageStatus').html('<div class="alert alert-success" role="alert">Status changed successfully...</div>');
                            // console.log(result);
                        });
                    }

                });


                $('.btn_ready_to_ship').click(function() {
                    datatable.destroy();
                    var status = 'ready-to-ship';
                    dataTables_hide_column("{{ route('data order') }}?status=" + status);
                    $(".btn_process_order").addClass('hide');
                });


                $('.filter_status li').click(function() {
                    datatable.destroy();
                   
                    $('.filter_status li.active').removeClass('active');
                    $(this).addClass("active");
                    var status = $(this).data("status");
                   
                    dataTables("{{ route('data order') }}?status=" + status);
                });

                function dataTables(url) {
                    // Datatable
                    datatable = $('#datatable').DataTable({
                        processing: true,
                        serverSide: true,
                        columnDefs: [{
                            'targets': 0,
                            'checkboxes': {
                                'selectRow': true
                            }
                        },
                            {
                                'visible': false,
                                'targets': 6
                            }
                        ],
                        // select : {
                        //     style: 'multi'
                        // },
                        order: [
                            [3, 'desc']
                        ],
                        ajax: url,
                        columns: [

                            {
                                name: 'checkbox',
                                data: 'checkbox'
                            },
                            {
                                name: 'order_id',
                                data: 'order_id'
                            },
                            {
                                name: 'website_id',
                                data: 'website_id'
                            },
                            {
                                name: 'order_date',
                                data: 'order_date'
                            },
                            {
                                name: 'customer_name',
                                data: 'customer_name'
                            },
                            // {
                            //     name: 'qrCode',
                            //     data: 'qrCode'
                            // },

                            {
                                name: 'shipping_method',
                                data: 'shipping_method'
                            },

                            {
                                name: 'label_printed',
                                data: 'label_printed'
                            },
                            {
                                name: 'payment_method_title',
                                data: 'payment_method_title'
                            },
                            {
                                name: 'total',
                                data: 'total'
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

                function dataTables_hide_column(url) {
                    // Datatable
                    datatable = $('#datatable').DataTable({
                        processing: true,
                        serverSide: true,
                        columnDefs: [{
                            'targets': 0,
                            'checkboxes': {
                                'selectRow': true
                            }
                        },
                            {
                                'visible': false,
                                'targets': 6
                            }
                        ],
                        // select : {
                        //     style: 'multi'
                        // },
                        order: [
                            [1, 'desc']
                        ],
                        ajax: url,
                        columns: [
                            {
                                name: 'checkbox',
                                data: 'checkbox'
                            },
                            {
                                name: 'order_id',
                                data: 'order_id'
                            },
                            {
                                name: 'website_id',
                                data: 'website_id'
                            },
                            {
                                name: 'order_date',
                                data: 'order_date'
                            },
                            {
                                name: 'customer_name',
                                data: 'customer_name'
                            },
                            // {
                            //     name: 'qrCode',
                            //     data: 'qrCode'
                            // },

                            {
                                name: 'shipping_method',
                                data: 'shipping_method'
                            },

                            {
                                name: 'label_printed',
                                data: 'label_printed'
                            },
                            {
                                name: 'payment_method_title',
                                data: 'payment_method_title'
                            },
                            {
                                name: 'total',
                                data: 'total'
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


                $(document).on('change', '#bulk_action', function() {
                    var status = $(this).val();
                    var rows_selected = datatable.column(0).checkboxes.selected();

                    var arr = [];
                    $.each(rows_selected, function(index, rowId) {
                        arr[index] = rowId;
                        //datatable.cell(index,9).data(status);
                    });

                    if (arr.length === 0) {
                        alert("Please Select At Least 1 Row...");
                        return;
                    }
                    var jSonData = JSON.stringify(arr);

                    $.ajax({
                        url: '{{ route('data bulkStatus') }}',
                        type: "POST",
                        data: {
                            'jSonData': jSonData,
                            'status': status,
                            '_token': $('meta[name=csrf-token]').attr('content')
                        },
                        beforeSend: function() {
                            $('.modal-message').removeClass('modal-hide');
                            $('#form-message').html('Please Wait .....');
                            // $('#messageStatus').html();
                        }
                    }).done(function(result) {
                        $.each(rows_selected, function(index, rowId) {
                            arr[index] = rowId;
                            datatable.cell(index, 9).data(status);
                        });
                        //$('.modal-message').addClass('modal-hide');
                        $('#form-message').html('<div class="alert alert-success" role="alert">Status changed successfully...</div>');
                        // console.log(result);
                    });
                });


                $(document).on('click', '.sync_selected li', function() {
                    var rows_selected = datatable.column(0).checkboxes.selected();
                    console.log(rows_selected, 88888);

                    var arr = [];
                    $.each(rows_selected, function(index, rowId) {
                        arr[index] = rowId;
                    });

                    if (arr.length === 0) {
                        alert("Please Select At Least 1 Row...");
                        return;
                    }
                    var jSonData = JSON.stringify(arr);

                    $.ajax({
                        url: '{{ route('data bulkSync') }}',
                        type: "POST",
                        data: {
                            'jSonData': jSonData,
                            'status': status,
                            '_token': $('meta[name=csrf-token]').attr('content')
                        },
                        beforeSend: function() {
                            $('.modal-message').removeClass('modal-hide');
                            $('#form-message').html("Please wait...");
                        }
                    }).done(function(result) {
                        $('#form-message').html('<div class="alert alert-success" role="alert">Orders Synchronized successfully...</div>');
                        $('.modal-message').addClass('modal-hide');
                        location.reload();
                        //console.log(result);
                    });
                });

                $(document).on('click', '#BtnAddress', function() {

                    $('.modal-producut').removeClass('modal-hide');
                    $.ajax({
                        url: '{{ route('data customer address') }}?id=' + $(this).data('id'),
                        beforeSend: function() {
                            $('#form-producut').html('Loading');
                        }
                    }).done(function(result) {
                        $('#form-producut').html(result);
                    });
                });

                $(document).on('click', '#BtnUpdateStatus', function() {

                    //alert('Row index: ' + $(this).closest('tr').index());
                    $('.modal-producut').removeClass('modal-hide');
                    $.ajax({
                        url: '{{ route('data order status') }}?id=' + $(this).data('id') + '&&row_index=' + $(this).closest('tr').index(),
                        beforeSend: function() {
                            $('#form-producut').html('Loading');
                        }
                    }).done(function(result) {
                        $('#form-producut').html(result);
                        //  console.log(result);
                    });
                });

                $(document).on('click', '#BtnSubmitChangeStatus', function() {
                    $('.modal-producut').removeClass('modal-hide');
                    var row_index = $(this).data('row_index');
                    var status = $("#status").val();
                    $.ajax({
                        url: '{{ route('wc_change_order_purchase_status') }}',
                        type: 'post',
                        data: {
                            'id': $(this).data('id'),
                            'website_id': $(this).data('website_id'),
                            'order_id': $(this).data('order_id'),
                            'status': $("#status").val(),
                            '_token': $('meta[name=csrf-token]').attr('content')
                        },
                        beforeSend: function() {
                            $('#messageStatusSubmit').html("Please wait...");
                        }
                    }).done(function(result) {
                        datatable.cell(row_index, 9).data(status);
                        $('#messageStatusSubmit').html(result);
                    });
                });

                $(document).on('click', '#BtnProduct', function() {

                    $('.modal-producut').removeClass('modal-hide');
                    $.ajax({
                        url: '{{ route('data order products') }}?id=' + $(this).data('id'),
                        beforeSend: function() {
                            $('#form-producut').html('Loading');
                        }
                    }).done(function(result) {
                        $('#form-producut').html(result);
                    });
                });

                $(document).on('click', '#closeModalproduct', function() {
                    $('.modal-producut').addClass('modal-hide');

                });

                $(document).on('click', '#closeModalMessage', function() {
                    $('.modal-message').addClass('modal-hide');

                });


                $('#BtnInsert').click(function() {
                    $('.modal-insert').removeClass('modal-hide');
                });

                $(document).on('click', '#BtnUpdate', function() {
                    $('.modal-update').removeClass('modal-hide');
                    $.ajax({
                        url: '{{ route('data product') }}?id=' + $(this).data('id'),
                        beforeSend: function() {
                            $('#form-update').html('Loadin');
                        }
                    }).done(function(result) {
                        $('#form-update').html(result);
                    });
                });

                $(document).on('click', '#closeModalUpdate', function() {
                    $('.modal-update').addClass('modal-hide');
                });

                $('#closeModalInsert').click(function() {
                    $('.modal-insert').addClass('modal-hide');
                });

                $(document).on('click', '.BtnDelete', function() {
                    let drop = confirm('Are you sure?');
                    if (drop) {
                        var id = $(this).data('id');
                        var order_id = $(this).data('order_id');

                        $("#tr_" + id).addClass("current2");

                        $.ajax({
                            url: '{{ route('wc_order_delete') }}',
                            type: 'post',
                            data: {
                                'id': $(this).data('id'),
                                'order_id': $(this).data('order_id'),
                                '_token': $('meta[name=csrf-token]').attr('content')
                            },
                            beforeSend: function() {
                                // Pesan yang muncul ketika memproses delete
                            }
                        }).done(function(result) {
                            if (result.status === 1) {
                                // Pesan jika data berhasil di hapus
                                alert('Data deleted successfully');
                                $("#tr_" + id).hide();
                            } else {
                                alert(result.message);
                            }
                            location.reload();
                        });
                    }
                });


                $(document).on('click', '#BtnQuantity', function() {
                    $('.modal-quantity').removeClass('modal-hide');
                    $.ajax({
                        url: '{{ route('product data seller') }}?id=' + $(this).data('id'),
                        beforeSend: function() {
                            $('#form-quantity').html('Loading');
                        }
                    }).done(function(result) {
                        $('#form-quantity').html(result);
                    });
                });

                $(document).on('click', '#closeModalQuantity', function() {
                    $('.modal-quantity').addClass('modal-hide');
                });

                $('.new_form').on('submit', function(e) {
                    var form = this;
                    var rows_selected = datatable.column(0).checkboxes.selected();

                    $.each(rows_selected, function(index, rowId) {
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

        <script>
            $(document).ready(function() {
                $('.js-example-basic-single3').select2({
                    placeholder: "Select A Shop Name"
                });
            });
        </script>

        <script type="text/javascript">
            $(document).ready(function() {
                $('#btn_sync_order').click(function() {
                    var website_id = $('#SyncModalOrder #shop option:selected').val();

                    var number_of_orders = $('input[name="number_of_orders"]').val();

                    var site_url = $('#SyncModalOrder #shop option:selected').attr('data-site_url');
                    var consumer_key = $('#SyncModalOrder #shop option:selected').attr('data-key');
                    var consumer_secret = $('#SyncModalOrder #shop option:selected').attr('data-secrete');
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

                    $('.message_sync').html('<div class="alert alert-warning" role="alert">Proccesing...Please wait a bit </div>');
                    $.ajax({
                        url: '{{ route('wc_orders_sync_manually') }}',
                        type: 'POST',
                        data: {
                            'number_of_orders': number_of_orders,
                            'website_id': website_id,
                            'site_url': site_url,
                            'consumer_key': consumer_key,
                            'consumer_secret': consumer_secret,
                            'page': 1,
                            'limit': 100,
                            'per_page': 100,
                            '_token': $('meta[name=csrf-token]').attr('content')
                        },
                        success: function(data) {
                            console.log(data);

                            $('.message_sync').html('<div class="alert alert-success" role="alert">Orders are Synchronized successfully...</div>');
                            location.reload();
                        }
                    });
                });
            });
        </script>

        <script type="text/javascript">
            $(document).ready(function() {
                $('#btn_sync_countries_state').click(function() {
                    var website_id = $('#SyncModalCountriesState #shop option:selected').val();
                    var site_url = $('#SyncModalCountriesState #shop option:selected').attr('data-site_url');
                    var consumer_key = $('#SyncModalCountriesState #shop option:selected').attr('data-key');
                    var consumer_secret = $('#SyncModalCountriesState #shop option:selected').attr('data-secrete');
                    if (typeof consumer_key === "undefined") {

                        $('.message_sync_country_state').html('<div class="alert alert-danger" role="alert">Please Select A Shop</div>');
                        return;
                    }
                    if (consumer_key === "") {
                        $('.message_sync_country_state').html('<div class="alert alert-danger" role="alert">Please add REST API Consumer Key</div>');
                        return;
                    }

                    if (consumer_secret === "") {
                        $('.message_sync_country_state').html('<div class="alert alert-danger" role="alert">Please add REST API Consumer Secrete</div>');
                        return;
                    }

                    $(".page-item").removeClass("active");
                    $(this).parents("li").addClass("active");
                    $('.message_sync_country_state').html('<div class="alert alert-warning" role="alert">Proccesing...</div>');
                    $.ajax({
                        url: '{{ route('wc_country_state_sync') }}',
                        type: 'post',
                        data: {
                            'website_id': website_id,
                            'site_url': site_url,
                            'consumer_key': consumer_key,
                            'consumer_secret': consumer_secret,
                            'page': 1,
                            'limit': 100,
                            'per_page': 100,
                            '_token': $('meta[name=csrf-token]').attr('content')
                        },
                        success: function(data) {
                            //console.log(data);

                            $('.message_sync_country_state').html('<div class="alert alert-success" role="alert">Orders are Synchronized successfully...</div>');
                            location.reload();
                        }
                    });
                });



                $(document).on('click', '#printLevel', function() {
                    $("#pack_order_modal").modal('hide');
                    $("#print_level_modal").modal('show');
                    var shipment_id = $(this).data('id');
                    var order_id = $(this).attr('order-id');
                    var shop_id = $(this).attr('shop_id');
                    $("#shipment_id_input_val").val(shipment_id);
                    $("#shop_id_input_val").val(shop_id);
                    $("#order_id_input_val").val(order_id);
                    $("#order_id_div").text('Order ID #'+order_id);
                    //$("#shipment_id_div").text('Shipment ID #'+shipment_id);
                    $.ajax
                    ({
                        type: 'GET',
                        data: { shop_id:shop_id,shipment_id:shipment_id, order_id:order_id},
                        url: '{{url('getWCCustomerOrderHistory')}}',
                        success: function(result)
                        {
                            console.log(result);
                            $("#order_details").html(result);
                        }
                    });
                });



            });
        </script>
    @endpush

</x-app-layout>
