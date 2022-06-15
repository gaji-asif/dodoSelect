<x-app-layout>

    @section('title')
        {{ __('translation.Order Purchase') }}
    @endsection

    @push('bottom_css')
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/jquery-datatables-checkboxes@1.2.12/css/dataTables.checkboxes.css">

        <style>
            #BtnSyncModalProduct{
                float:right;
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

            .filter_status li.active  {
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
        </style>
    @endpush


    <div class="col-span-12">
        <div class="row">
            <div class="col-lg-12 mt-3">
                <div class="card">
                    <div class="card-body">
                        @include('settings.menu')
                    </div>
                </div>
            </div>
            <div class="col-lg-12 mt-5">
                <div class="card">
                    <div class="card-body">
                        <div class="card-title">
                            <h4>
                                <strong>Cron reports</strong>
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

                        <div id="messageStatus"></div>

                        <div class="overflow-x-auto">
                            <table class="table-auto border-collapse w-full border mt-4" id="datatable">
                                <thead class="border bg-green-300">
                                    <tr class="rounded-lg text-sm font-medium text-gray-700 text-left">
                                        <th class="px-4 py-2 border-2 text-center">Website</th>
                                        <th class="px-4 py-2 border-2 text-center">Type</th>
                                        <th class="px-4 py-2 border-2 text-center">Number of record</th>
                                        <th class="px-4 py-2 border-2 text-center">Result</th>
                                        <th class="px-4 py-2 border-2 text-center">Cron time</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>


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
    </div>


    @push('bottom_js')
        <script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/jquery-datatables-checkboxes@1.2.12/js/dataTables.checkboxes.min.js"></script>

        <script>
            $(document).ready(function() {
                $(document).on('change', '#website_id', function() {
                    datatable.destroy();

                    var website_id = $(this).val();
                    dataTables("{{ route('data product') }}?website_id=" + website_id);
                    $(".btn_process_order").removeClass('hide');
                });


                dataTables("{{ route('cronReport') }}");
                var datatable;


                function dataTables(url) {
                    // Datatable
                    datatable = $('#datatable').DataTable({
                        processing: true,
                        serverSide: true,

                        // select : {
                        //     style: 'multi'
                        // },
                        order: [[4, 'desc']],
                        ajax: url,
                        columns: [

                            {
                                name: 'name',
                                data: 'name'
                            },


                            {
                                name: 'type',
                                data: 'type'
                            },


                            {
                                name: 'number_of_record_updated',
                                data: 'number_of_record_updated'
                            },

                            {
                                name: 'result',
                                data: 'result'
                            },
                            {
                                name: 'created_at',
                                data: 'created_at'
                            }
                        ]
                    });
                }


                $('#BtnInsert').click(function() {
                    $('.modal-insert').removeClass('modal-hide');
                });


                $(document).on('click', '#closeModalproduct', function() {
                    $('.modal-producut').addClass('modal-hide');
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

                        $("#tr_"+id).addClass("current2");

                        $.ajax({
                            url: '{{ route('wc_product_delete') }}',
                            type: 'post',
                            data: {
                            'id': $(this).data('id'),
                                '_token': $('meta[name=csrf-token]').attr('content')
                            },
                            beforeSend: function() {
                                // Pesan yang muncul ketika memproses delete
                            }
                        }).done(function(result) {
                            if (result.status === 1) {
                                // Pesan jika data berhasil di hapus
                                alert('Data deleted successfully');
                                $("#tr_"+id).hide();
                            } else {
                                alert(result.message);

                            }
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

        <script>
            $( document ).ready(function() {
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
                        type: 'get',
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
                        type: 'get',
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

        <script>
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
                            $('.message_sync_country_state').html('<div class="alert alert-success" role="alert">Orders are Synchronized successfully...</div>');
                            location.reload();
                        }
                    });
                });
            });
        </script>
    @endpush

</x-app-layout>
