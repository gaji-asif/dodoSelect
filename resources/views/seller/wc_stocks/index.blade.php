<x-app-layout>

    @section('title')
        {{ __('translation.Inventories') }}
    @endsection

    @push('bottom_css')
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/jquery-datatables-checkboxes@1.2.12/css/dataTables.checkboxes.css">
    @endpush


        @if(\App\Models\Role::checkRolePermissions('Can access menu: WooCommerce - Inventory'))
            <div class="col-span-12">

        <div class="card">
            <div class="card-body">
                <div class="card-title">
                    <h4>
                        <strong>Inventories</strong>
                    </h4>
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

                <div class="alert alert-success mb-3 background-success messageStatus2 d-none" role="alert">
                    <div id="messageStatus"></div>
                </div>

                <div class="modal fade bd-example-modal-lg" id="addProduct" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-lg" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                              <div class="row" style="width: 100%">
                                    <div class="col-md-6">
                                        <h5 class="modal-title" id="exampleModalLabel">
                                            <strong>Add products</strong>
                                        </h5>
                                    </div>
                                </div>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <form action="{{route("inventoryToProduct")}}" class="inventoryToProduct">
                                @csrf
                                <div class="row" style="width: 100%">
                                    <div class="col-lg-12">
                                        <div class="alert alert-warning alert-dismissible fade show d-none" role="alert" id="modal_error_inventory_div_2">
                                            <div class="modal_error_inventory_2"></div>
                                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                    </div>

                                    <div class="col-lg-6 mb-3">
                                        <lebel for="product_id">Search product to add on this inventory</lebel>
                                        <select class="js-data-example-ajax product_id" id="product_id" name="product_id"></select>
                                    </div>
                                    <div class="col-lg-4 mb-3">
                                        <button id="btn_sync_product" type="submit" class="btn btn-success mt-16">Add</button>
                                        <button id="sync_product" type="button" class="btn btn-success mt-16">
                                            <span>
                                                <div class="fa-1x">
                                                    Sync the checked product
                                                    <i class="fas fa-spinner fa-spin d-none" id="spin"></i>
                                                </div>
                                            </span>
                                        </button>
                                    </div>
                                    <input type="hidden" id="inventory_id"  class="inventory_id">

                                    <div class="col-lg-12">
                                        <table class="table-auto border-collapse w-full border mt-4" id="datatable2">
                                            <thead class="border bg-green-300">
                                            <tr class="rounded-lg text-sm font-medium text-gray-700 text-left">
                                                <th>&nbsp;</th>
                                                <th>&nbsp;</th>
                                                <th class="px-4 py-2 border-2 text-center">Product Id</th>
                                                <th class="px-4 py-2 border-2 text-center">Photo</th>
                                                <th class="px-4 py-2 border-2 text-center">Website</th>
                                                <th class="px-4 py-2 border-2 text-center">Type</th>
                                                <th class="px-4 py-2 border-2 text-center">Product Name</th>
                                                <th class="px-4 py-2 border-2 text-center">Product Code</th>
                                                <th class="px-4 py-2 border-2 text-center">Quantity</th>
                                                <th class="px-4 py-2 border-2 text-center d-none" >Price</th>
                                                <th class="px-4 py-2 border-2 text-center">Manage</th>
                                            </tr>
                                            </thead>
                                            <tbody></tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn bg-green-500 text-white" data-dismiss="modal">Close</button>
                            </div>
                            </form>
                        </div>
                  </div>
                </div>


                <div class="col-lg-12">
                    <x-button class="mb-6" color="green"  data-toggle="modal" data-target="#SyncModalProduct11"  id="BtnSyncModalProduct">
                        <p class="mr-1">Create inventory</p>
                    </x-button>
                </div>

                <div class="modal fade" id="SyncModalProduct11" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="exampleModalLabel"><strong>Create inventory</strong></h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body" id="modal_body_inventory">
                                <form action="{{route('createInventory')}}" class="createInventory">
                                    @csrf
                                <div class="col-lg-12">
                                    <div class="alert alert-warning alert-dismissible fade show d-none" role="alert" id="modal_error_inventory_div">
                                    <div class="modal_error_inventory"></div>
                                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                </div>
                                <div class="col-lg-12">
                                    <div class="form-group">
                                        <label for="exampleInputEmail1">Name</label>
                                        <input required type="text" class="form-control" name="inventory_name">
                                    </div>
                                    <div class="form-group">
                                        <label for="exampleInputEmail1">Code</label>
                                        <input required type="text" class="form-control" name="inventory_code">
                                    </div>
                                    <div class="form-group">
                                        <label for="exampleInputEmail1">Quantity</label>
                                        <input required type="number" class="form-control" name="quantity">
                                    </div>
                                </div>
                                <div class="col-lg-12 message_sync"></div>
                            </div>
                            <div class="modal-footer" id="invtBTn">
                                <button type="button" class="btn bg-green-500 text-white" data-dismiss="modal">Close</button>
                                <button id="btn_sync_product" type="submit" class="btn btn-success">Create</button>
                            </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="modal fade" id="editEnv" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="exampleModalLabel"><strong>Edit inventory</strong></h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body" id="modal_body_inventory">
                                <form action="{{route('editInventory')}}" class="createInventory2">
                                    @csrf
                                <div class="col-lg-12">
                                    <div class="alert alert-warning alert-dismissible fade show d-none" role="alert" id="modal_error_inventory_div">
                                       <div class="modal_error_inventory"></div>
                                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                </div>
                                <div class="col-lg-12">
                                    <input type="hidden" name="id" id="inv_id">
                                    <div class="form-group">
                                        <label for="exampleInputEmail1">Name</label>
                                        <input required type="text" class="form-control" name="inventory_name" id="inventory_name">
                                    </div>
                                    <div class="form-group">
                                        <label for="exampleInputEmail1">Code</label>
                                        <input required type="text" class="form-control" name="inventory_code" id="inventory_code">
                                    </div>
                                    <div class="form-group">
                                        <label for="exampleInputEmail1">Quantity</label>
                                        <input required type="number" class="form-control" name="quantity" id="quantity">
                                    </div>
                                </div>
                                <div class="col-lg-12 message_sync"></div>
                            </div>
                            <div class="modal-footer" id="invtBTn">
                                <button type="submit" class="btn bg-green-500 text-white" data-dismiss="modal">Close</button>
                                <button id="btn_sync_product" type="submit" class="btn btn-success">Update</button>
                            </div>
                            </form>
                        </div>
                    </div>
                </div>


                <div class="w-full overflow-x-auto">
                    <table class="table-auto border-collapse w-full border mt-4" id="datatable">
                        <thead class="border bg-green-300">
                            <tr class="rounded-lg text-sm font-medium text-gray-700 text-left">
                                <th class="px-4 py-2 border-2 text-center">Inventory Id</th>
                                <th class="px-4 py-2 border-2 text-center">Date</th>
                                <th class="px-4 py-2 border-2 text-center">Inventory Name</th>
                                <th class="px-4 py-2 border-2 text-center">Inventory Code</th>
                                <th class="px-4 py-2 border-2 text-center">Quantity</th>
                                <th style="width: 177px !important;" class="px-4 py-2 border-2 text-center">Manage</th>
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
        <div style="background-color: rgba(0,0,0,0.5)"
            class="overflow-auto fixed  inset-0 z-10 flex items-center justify-center">
            <div class="bg-white w-11/12 md:max-w-md overflow-y-auto mx-auto rounded shadow-lg pt-4 pb-6 text-left px-6"
                x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 scale-90"
                x-transition:enter-end="opacity-100 scale-100">

                <div style="max-height:90vh" id="form-producut"></div>
            </div>
        </div>
    </div>


    @push('bottom_js')
        <script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/jquery-datatables-checkboxes@1.2.12/js/dataTables.checkboxes.min.js"></script>

        <script>
            searchResult('.js-data-example-ajax', 'brands');

            function searchResult(tag, type) {
                $(tag).select2({
                    width: '100%',
                    ajax: {
                        url: "{{route('autocomplete.fetch')}}",
                        dataType: 'json',
                        delay: 250,
                        data: function (params) {
                            return {
                                q: params.term, // search term
                                page: params.page
                            };
                        },
                        processResults: function (data, params) {
                            // parse the results into the format expected by Select2
                            // since we are using custom formatting functions we do not need to
                            // alter the remote JSON data, except to indicate that infinite
                            // scrolling can be used
                            params.page = params.page;

                            return {
                                results: data.items,
                                pagination: {
                                    more: (params.page * 30) < data.total_count
                                }
                            };
                        },
                        cache: true
                    },
                    placeholder: 'Search for a repository',
                    minimumInputLength: 1,
                    templateResult: formatRepo,
                    templateSelection: formatRepoSelection
                });
            }


            function formatRepo(repo) {
                var $container = $(
                    "<div class='select2-result-repository clearfix d-flex'>" +
                    "<div class='select2-result-repository__avatar'><img width='60px' class='select2-result-repository__avatar_file' src='' /></div>" +
                    "<div class='select2-result-repository__meta'>" +
                    "<div class='select2-result-repository__title'></div>" +
                    "<p class=''><b class='select2-result-repository__shop'></b></p>" +
                    "</div>" +
                    "</div>" +
                    "</div>"
                );


                $container.find(".select2-result-repository__avatar_file").attr('src', repo.image );
                $container.find(".select2-result-repository__title").text(repo.full_name);
                $container.find(".select2-result-repository__shop").text(repo.shop_name);
                return $container;
            }

            function formatRepoSelection(repo) {
                return repo.full_name;
            }

        </script>

        <script>
            $(document).ready(function() {
                dataTables("{{ route('inventories') }}");
                var datatable;

                function dataTables(url) {
                    // Datatable
                    datatable = $('#datatable').DataTable({
                        processing: true,
                        serverSide: true,
                        columnDefs : [
                        { width: 30, targets: 0 },
                        { width: 70, targets: 1 },
                        { width: 80, targets: 2 },
                        { width: 100, targets: 3 },
                        { width: 200, targets: 4 },
                        { width: 120, targets: 5 },
                        ],
                        order: [[2, 'desc']],
                        ajax: url,
                        columns: [
                            {
                                name: 'inventory_id',
                                data: 'inventory_id'
                            },
                            {
                                name: 'created_at',
                                data: 'created_at'
                            },
                            {
                                name: 'inventory_name',
                                data: 'inventory_name'
                            },
                            {
                                name: 'inventory_code',
                                data: 'inventory_code'
                            },

                            {
                                name: 'quantity',
                                data: 'quantity'
                            },

                            {
                                name: 'manage',
                                data: 'manage'
                            }
                        ]
                    });

                    // Add event listener for opening and closing details
                    $('#datatable tbody').on('click', 'td', function () {
                        var tr = $(this).closest('tr');
                        var row = datatable.row( tr );
                        var row_index = $(this).closest('tr').index()
                        //alert('Row index: ' + datatable.rows( { selected: true } ).data()[row_index]['checkbox']);
                        if ( row.child.isShown() ) {
                            // This row is already open - close it
                            row.child.hide();
                            tr.removeClass('shown');
                        }
                        else {
                            // Open this row
                            row.child( format(row.data()) ).show();
                            tr.addClass('shown');
                        }
                    });

                    /* Formatting function for row details - modify as you need */
                    function format ( d ) {
                        // `d` is the original data object for the row
                        var web_id_product_id = d.checkbox;

                        $.ajax({
                            url: '{{ route('data get_inventories_variations_by_id') }}?id_details=' + web_id_product_id,
                            beforeSend: function() {
                                //$('#form-producut').html('Loading');
                            }
                        }).done(function(result) {
                            $('.child_table').html(result);
                            //console.log(result);
                        });

                        return '<table class="child_table table"> </table>';
                    }
                }


                $(document).on('click', '.BtnEditQty', function() {
                    $('.modal-producut').removeClass('modal-hide');
                    $.ajax({
                        url: '{{ route('data inventory products') }}?product_code=' + $(this).data('product_code')+'&&row_index='+$(this).closest('tr').index(),
                        beforeSend: function() {
                            $('#form-producut').html('Loading');
                        }
                    }).done(function(result) {
                        $('#form-producut').html(result);
                    });
                });


                $(document).on('click', '.BtnUpdate', function() {
                    //  alert('Row index: ' + $(this).closest('tr').index());
                    $('.modal-producut').removeClass('modal-hide');
                    $.ajax({
                        url: '{{ route('data stock_edit_form') }}?id=' + $(this).data('id')+'&&row_index='+$(this).closest('tr').index(),
                        beforeSend: function() {
                            $('#form-producut').html('Loading');
                        }
                    }).done(function(result) {
                        $('#form-producut').html(result);
                    //  console.log(result);
                    });
                });


                $(document).on('submit', '.createInventory', function(event) {
                    event.preventDefault();
                    //  alert('Row index: ' + $(this).closest('tr').index());
                    $.ajax({
                        url: '{{ route('createInventory') }}?name=' + $('input[name^="inventory_name"]').val()+'&code='+$('input[name^="inventory_code"]').val()+"&quantity="+$('input[name^="quantity"]').val(),
                        beforeSend: function() {
                            $('#modal_error_inventory_div').addClass("d-none");
                            // $('#modal_body_inventory').html('Loading');
                        }
                    }).done(function(result) {
                        console.log(result,"resultresult");
                        if (result.success == true){
                            $('#modal_error_inventory_div').removeClass("d-none");
                            $('.modal_error_inventory').html(result.message);

                            $('#datatable').each(function() {
                                dt = $(this).dataTable();
                                dt.fnDraw();
                            })
                        }else {

                            $('#modal_error_inventory_div').removeClass("d-none");
                            $('.modal_error_inventory').html(result.message);
                        }
                        //  console.log(result);
                    });
                });


                $(document).on('submit', '.inventoryToProduct', function(event) {
                    event.preventDefault();
                    //  alert('Row index: ' + $(this).closest('tr').index());
                    $.ajax({
                        url: '{{ route('inventoryToProduct') }}?product_id=' + $('#product_id').val()+'&inventory_id='+$('#inventory_id').val(),
                        beforeSend: function() {
                            $('#modal_error_inventory_div_2').addClass("d-none");
                            // $('#modal_body_inventory').html('Loading');
                        }
                    }).done(function(result) {
                    console.log(result,"resultresult");
                        if (result.success == true){
                            $('#modal_error_inventory_div_2').removeClass("d-none");
                            $('.modal_error_inventory_2').html(result.message);

                            $('#datatable2').DataTable().destroy();
                            dataTables2("{{ route('inventoryProduct') }}");
                            var datatable2;
                            function dataTables2(url) {
                                console.log($('#inventory_id').val(),"$('#inventory_id').val()$('#inventory_id').val()")
                                // Datatable
                                datatable = $('#datatable2').DataTable({
                                    processing: true,
                                    serverSide: true,
                                    order: [[2, 'desc']],
                                    ajax: url+"?inv_id="+$('#inventory_id').val(),
                                    columns: [
                                        {
                                            name: 'expand',
                                            data:'expand',
                                        },


                                        {
                                            name: 'checkbox',
                                            data: 'checkbox'
                                        },
                                        {

                                            name: 'product_id',
                                            data: 'product_id'
                                        },
                                        {
                                            name: 'image',
                                            data: 'image'
                                        },


                                        {
                                            name: 'website_id',
                                            data: 'website_id'
                                        },


                                        {
                                            name: 'type',
                                            data: 'type'
                                        },

                                        {
                                            name: 'product_name',
                                            data: 'product_name'
                                        },

                                        {
                                            name: 'product_code',
                                            data: 'product_code'
                                        },

                                        {
                                            name: 'quantity',
                                            data: 'quantity'
                                        },
                                        {
                                            name: 'manage',
                                            data: 'manage'
                                        }
                                    ]
                                });

                                // Add event listener for opening and closing details
                                {{--$('#datatable tbody').on('click', 'td', function () {--}}
                                {{--    var tr = $(this).closest('tr');--}}
                                {{--    var row = datatable.row( tr );--}}
                                {{--    var row_index = $(this).closest('tr').index()--}}
                                {{--    //alert('Row index: ' + datatable.rows( { selected: true } ).data()[row_index]['checkbox']);--}}
                                {{--    if ( row.child.isShown() ) {--}}
                                {{--        // This row is already open - close it--}}
                                {{--        row.child.hide();--}}
                                {{--        tr.removeClass('shown');--}}
                                {{--    }--}}
                                {{--    else {--}}
                                {{--        // Open this row--}}
                                {{--        row.child( format(row.data()) ).show();--}}
                                {{--        tr.addClass('shown');--}}
                                {{--    }--}}
                                {{--});--}}

                                {{--/* Formatting function for row details - modify as you need */--}}
                                {{--function format ( d ) {--}}
                                {{--    // `d` is the original data object for the row--}}
                                {{--    var web_id_product_id = d.checkbox;--}}

                                {{--    $.ajax({--}}
                                {{--        url: '{{ route('data get_inventories_variations_by_id') }}?id_details=' + web_id_product_id,--}}
                                {{--        beforeSend: function() {--}}
                                {{--            //$('#form-producut').html('Loading');--}}
                                {{--        }--}}
                                {{--    }).done(function(result) {--}}
                                {{--        $('.child_table').html(result);--}}
                                {{--        //console.log(result);--}}
                                {{--    });--}}
                                {{--    return '<table class="child_table table"> </table>';--}}
                                {{--}--}}
                            }

                        }else {

                            $('#modal_error_inventory_div_2').removeClass("d-none");
                            $('.modal_error_inventory_2').html(result.message);
                        }

                        //  console.log(result);
                    });
                });

                $('#BtnInsert').click(function() {
                $('.modal-insert').removeClass('modal-hide');
            });


            $(document).on('click', '#closeModalproduct', function() {
                $('.modal-producut').addClass('modal-hide');
                //alert();
                $.ajax({
                    url: '{{ route('delete_session_add_linked_product') }}',
                    type: 'post',
                    data: {
                    'id': $(this).data('id'),
                        '_token': $('meta[name=csrf-token]').attr('content')
                    },
                    beforeSend: function() {
                        // Pesan yang muncul ketika memproses delete
                    }
                })
            });


            $(document).on('click', '#closeModalUpdate', function() {
                $('.modal-update').addClass('modal-hide');
            });


            $('#closeModalInsert').click(function() {
                $('.modal-insert').addClass('modal-hide');
            });


            $(document).on('click', '.BtnDelete2', function() {
                let drop = confirm('Are you sure?');
                if (drop) {
                    var id = $(this).data('id');
                    var order_id = $(this).data('order_id');

                    $("#tr_"+id).addClass("current2");

                    $.ajax({
                    url: '{{ route('inventoriesDelete') }}',
                        type: 'post',
                        data: {
                        'id': $(this).data('id'),
                            '_token': $('meta[name=csrf-token]').attr('content')
                        },
                        beforeSend: function() {
                            // Pesan yang muncul ketika memproses delete
                        }
                    }).done(function(result) {

                        if (result.succes == true) {
                            // Pesan jika data berhasil di hapus
                            alert(result.message);
                            $('#datatable').each(function() {
                                dt = $(this).dataTable();
                                dt.fnDraw();
                            })
                        } else {
                            alert(result.message);

                        }
                    });
                }
            });


            $(document).on('click', '.BtnDelete3', function() {
                let drop = confirm('Are you sure?');
                if (drop) {
                    var id = $(this).data('id');
                    var order_id = $(this).data('order_id');

                    $("#tr_"+id).addClass("current2");

                    $.ajax({
                        url: '{{ route('inventoryProductDelete') }}',
                        type: 'post',
                        data: {
                        'id': $(this).data('id'),
                            '_token': $('meta[name=csrf-token]').attr('content')
                        },
                        beforeSend: function() {
                            // Pesan yang muncul ketika memproses delete
                        }
                    }).done(function(result) {
                        if (result.succes == true) {
                            // Pesan jika data berhasil di hapus
                            alert(result.message);
                            $('#datatable2').DataTable().destroy();
                            dataTables2("{{ route('inventoryProduct') }}");
                            var datatable2;
                            function dataTables2(url) {
                                console.log($('#inventory_id').val(),"$('#inventory_id').val()$('#inventory_id').val()")
                                // Datatable
                                datatable = $('#datatable2').DataTable({
                                    processing: true,
                                    serverSide: true,
                                    order: [[2, 'desc']],
                                    ajax: url+"?inv_id="+$('#inventory_id').val(),
                                    columns: [
                                        {
                                            name: 'expand',
                                            data:'expand',
                                        },
                                        {
                                            name: 'checkbox',
                                            data: 'checkbox'
                                        },
                                        {
                                            name: 'product_id',
                                            data: 'product_id'
                                        },
                                        {
                                            name: 'image',
                                            data: 'image'
                                        },
                                        {
                                            name: 'website_id',
                                            data: 'website_id'
                                        },
                                        {
                                            name: 'type',
                                            data: 'type'
                                        },
                                        {
                                            name: 'product_name',
                                            data: 'product_name'
                                        },
                                        {
                                            name: 'product_code',
                                            data: 'product_code'
                                        },
                                        {
                                            name: 'quantity',
                                            data: 'quantity'
                                        },
                                        {
                                            name: 'manage',
                                            data: 'manage'
                                        }
                                    ]
                                });

                                // Add event listener for opening and closing details
                                $('#datatable tbody').on('click', 'td', function () {
                                    var tr = $(this).closest('tr');
                                    var row = datatable.row( tr );
                                    var row_index = $(this).closest('tr').index()
                                    //alert('Row index: ' + datatable.rows( { selected: true } ).data()[row_index]['checkbox']);
                                    if ( row.child.isShown() ) {
                                        // This row is already open - close it
                                        row.child.hide();
                                        tr.removeClass('shown');
                                    }
                                    else {
                                        // Open this row
                                        row.child( format(row.data()) ).show();
                                        tr.addClass('shown');
                                    }
                                });

                                /* Formatting function for row details - modify as you need */
                                function format ( d ) {
                                    // `d` is the original data object for the row
                                    var web_id_product_id = d.checkbox;

                                    $.ajax({
                                        url: '{{ route('data get_inventories_variations_by_id') }}?id_details=' + web_id_product_id,
                                        beforeSend: function() {
                                            //$('#form-producut').html('Loading');
                                        }
                                    }).done(function(result) {
                                        $('.child_table').html(result);
                                        //console.log(result);
                                    });
                                    return '<table class="child_table table"> </table>';
                                }
                            }
                        } else {
                            alert(result.message);
                        }
                    });
                }
            });


            $(document).on('click', '#sync_product', function() {
                var checked = [];
                $("input:checked").each(function () {
                    var id = $(this).data("id");
                    checked.push(id);
                });

                var inv_id = $("#inventory_id").val();

                $.ajax({
                    url: '{{ route('SyncProduct') }}?productIdes=' + checked.toString()+"&inv_id="+inv_id,
                    beforeSend: function() {
                        $("#spin").removeClass('d-none');
                        $('#modal_error_inventory_div_2').addClass("d-none");
                    }
                }).done(function(result) {
                    $("#spin").addClass("d-none");
                    $('#modal_error_inventory_div_2').removeClass("d-none");
                    $('.modal_error_inventory_2').html(result.message);
                    $('#datatable2').DataTable().destroy();
                    dataTables2("{{ route('inventoryProduct') }}");
                    var datatable2;
                    function dataTables2(url) {
                        console.log($('#inventory_id').val(),"$('#inventory_id').val()$('#inventory_id').val()")
                        // Datatable
                        datatable = $('#datatable2').DataTable({
                            processing: true,
                            serverSide: true,
                            order: [[2, 'desc']],
                            ajax: url+"?inv_id="+$('#inventory_id').val(),

                            columns: [
                                {
                                    name: 'expand',
                                    data:'expand',
                                },
                                {
                                    name: 'checkbox',
                                    data: 'checkbox'
                                },
                                {
                                    name: 'product_id',
                                    data: 'product_id'
                                },
                                {
                                    name: 'image',
                                    data: 'image'
                                },
                                {
                                    name: 'website_id',
                                    data: 'website_id'
                                },
                                {
                                    name: 'type',
                                    data: 'type'
                                },
                                {
                                    name: 'product_name',
                                    data: 'product_name'
                                },
                                {
                                    name: 'product_code',
                                    data: 'product_code'
                                },
                                {
                                    name: 'quantity',
                                    data: 'quantity'
                                },
                                {
                                    name: 'manage',
                                    data: 'manage'
                                }
                            ]
                        });

                        // Add event listener for opening and closing details
                        $('#datatable tbody').on('click', 'td', function () {
                            var tr = $(this).closest('tr');
                            var row = datatable.row( tr );
                            var row_index = $(this).closest('tr').index()
                            //alert('Row index: ' + datatable.rows( { selected: true } ).data()[row_index]['checkbox']);
                            if ( row.child.isShown() ) {
                                // This row is already open - close it
                                row.child.hide();
                                tr.removeClass('shown');
                            }
                            else {
                                // Open this row
                                row.child( format(row.data()) ).show();
                                tr.addClass('shown');
                            }
                        });

                        /* Formatting function for row details - modify as you need */
                        function format ( d ) {
                            // `d` is the original data object for the row
                            var web_id_product_id = d.checkbox;

                            $.ajax({
                                url: '{{ route('data get_inventories_variations_by_id') }}?id_details=' + web_id_product_id,
                                beforeSend: function() {
                                    //$('#form-producut').html('Loading');
                                }
                            }).done(function(result) {
                                $('.child_table').html(result);
                                //console.log(result);
                            });
                            return '<table class="child_table table"> </table>';
                        }
                    }
                });
            });


            $(document).on('click', '.addProduct', function() {
                $("#addProduct").modal("show");
                $('#datatable2').DataTable().destroy();
                $("#inventory_id").val($(this).data('id'));
                dataTables2("{{ route('inventoryProduct') }}");

                var datatable2;
                function dataTables2(url) {
                    console.log($('#inventory_id').val(),"$('#inventory_id').val()$('#inventory_id').val()")
                    // Datatable
                    datatable = $('#datatable2').DataTable({
                        processing: true,
                        serverSide: true,
                        order: [[2, 'desc']],
                        ajax: url+"?inv_id="+$('#inventory_id').val(),
                        columnDefs : [],
                        columns: [
                            {
                                name: 'expand',
                                data:'expand',
                            },
                            {
                                name: 'checkbox',
                                data: 'checkbox'
                            },
                            {

                                name: 'product_id',
                                data: 'product_id'
                            },
                            {
                                name: 'image',
                                data: 'image'
                            },
                            {
                                name: 'website_id',
                                data: 'website_id'
                            },
                            {
                                name: 'type',
                                data: 'type'
                            },
                            {
                                name: 'product_name',
                                data: 'product_name'
                            },
                            {
                                name: 'product_code',
                                data: 'product_code'
                            },
                            {
                                name: 'quantity',
                                data: 'quantity'
                            },
                            {
                                name: 'manage',
                                data: 'manage'
                            }
                        ]
                    });

                    // Add event listener for opening and closing details
                    $('#datatable tbody').on('click', 'td', function () {
                        var tr = $(this).closest('tr');
                        var row = datatable.row( tr );
                        var row_index = $(this).closest('tr').index()
                        //alert('Row index: ' + datatable.rows( { selected: true } ).data()[row_index]['checkbox']);
                        if ( row.child.isShown() ) {
                            // This row is already open - close it
                            row.child.hide();
                            tr.removeClass('shown');
                        }
                        else {
                            // Open this row
                            row.child( format(row.data()) ).show();
                            tr.addClass('shown');
                        }
                    });

                    /* Formatting function for row details - modify as you need */
                    function format ( d ) {
                        // `d` is the original data object for the row
                        var web_id_product_id = d.checkbox;

                        $.ajax({
                            url: '{{ route('data get_inventories_variations_by_id') }}?id_details=' + web_id_product_id,
                            beforeSend: function() {
                                //$('#form-producut').html('Loading');
                            }
                        }).done(function(result) {
                            $('.child_table').html(result);
                            //console.log(result);
                        });
                        return '<table class="child_table table"> </table>';
                    }
                }
            });


            $(document).on('click', '.editInv', function() {
                $("#editEnv").modal("show");
                $("#inventory_name").val($(this).data('inv_name'));
                $("#inventory_code").val($(this).data('inv_code'));
                $("#quantity").val($(this).data('inv_quantity'));
                $("#inv_id").val($(this).data('id'));
            });


            $(document).on('click', '.syncAll', function() {
                var x = $(this).data('id');
                var cla = ".class_"+x;
                $(cla).addClass("d-none");

                $.ajax({
                    url: '{{ route('SyncAllInv') }}?id=' + x,
                    beforeSend: function() {
                        $(cla).removeClass("d-none");
                        $('.messageStatus2').addClass("d-none");
                    }
                }).done(function(result) {
                    $(cla).addClass("d-none");
                    $('.messageStatus2').removeClass("d-none");
                    $('#messageStatus').html(result.message);
                });
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


            $('.new_form').on('submit', function(e){
                var form = this;

                var rows_selected = datatable.column(0).checkboxes.selected();

                $.each(rows_selected, function(index, rowId){
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
            $(document).ready(function() {
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
                        success: function (data){
                        //$('tbody').html(data);
                        $('.message_sync').html('<div class="alert alert-success" role="alert">Products are Synchronized successfully...</div>');
                        location.reload();
                        }
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
                $('#btn_sync_order').click(function () {
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

                    $(".page-item").removeClass("active");
                    $(this).parents("li").addClass("active");
                    $('.message_sync').html('<div class="alert alert-warning" role="alert">Proccesing...Please wait a bit </div>');

                    $.ajax({
                        url: '{{ route('wc_orders_sync') }}',
                        type: 'post',
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
                        success: function (data){
                        //$('tbody').html(data);
                        $('.message_sync').html('<div class="alert alert-success" role="alert">Orders are Synchronized successfully...</div>');
                        location.reload();
                        }
                    });
                });
            });
        </script>

        <script type="text/javascript">
            $(document).ready(function() {
                $('#btn_sync_countries_state').click(function () {
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
                        success: function (data){
                        //console.log(data);
                        $('.message_sync_country_state').html('<div class="alert alert-success" role="alert">Orders are Synchronized successfully...</div>');
                        location.reload();
                        }
                    });
                });
            });
        </script>
    @endpush

</x-app-layout>
