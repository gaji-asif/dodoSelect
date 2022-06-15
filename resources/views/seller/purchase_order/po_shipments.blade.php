<x-app-layout>
    @section('title', 'Shipments')

    @push('top_css')
        <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css">
    @endpush

    @if(\App\Models\Role::checkRolePermissions('Can access menu: Purchase Order - PO Shipments'))
        <x-card title="Shipments">
            <div class="flex flex-col lg:flex-row mb-10 lg:mb-4">
                <div class="w-full lg:w-1/4 mb-6 lg:mb-0">
                    <x-select name="supplier_id" id="supplier_id" style="width: 100%">
                        <option selected value="">
                            - {{ __('translation.Select Supplier Name') }} -
                        </option>
                        @foreach ($suppliers as $supplier)
                            <option value="{{ $supplier->id }}">
                                {{ $supplier->supplier_name }}
                            </option>
                        @endforeach
                    </x-select>
                </div>

                <div class="w-full lg:w-3/4 lg:ml-6 xl:ml-44">
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 sm:gap-2">
                        <x-page.order-purchase.card-filter label="{{ __('translation.All') }}" data-status="all" class="btn-status-filter active selected_status all">
                            {{ number_format($poShipmentTotalAll) }}
                        </x-page.order-purchase.card-filter>
                        <x-page.order-purchase.card-filter label="{{ __('translation.Open') }}" data-status="open" class="btn-status-filter open">
                            {{ number_format($poShipmentTotalOpen) }}
                        </x-page.order-purchase.card-filter>
                        <x-page.order-purchase.card-filter label="{{ __('translation.Arrive') }}" data-status="arrive" class="btn-status-filter arrive">
                            {{ number_format($poShipmentTotalArrive) }}
                        </x-page.order-purchase.card-filter>

                        <x-page.order-purchase.card-filter label="{{ __('translation.Close') }}" data-status="close" class="btn-status-filter closes">
                            {{ number_format($poShipmentTotalClose) }}
                        </x-page.order-purchase.card-filter>
                    </div>
                </div>
            </div>

            <div class="mt-6">
                @if (session('success'))
                    <x-alert-success>
                        {{ session('success') }}
                    </x-alert-success>
                @endif

                @if (session('danger'))
                    <x-alert-danger>
                        {{ session('danger') }}
                    </x-alert-danger>
                @endif

                @if (session('error'))
                    <x-alert-danger>
                        {{ session('error') }}
                    </x-alert-danger>
                @endif
            </div>

            <div class="overflow-x-auto">
                <table class="w-full" id="datatable">
                    <thead>
                    <tr>
                        <th class="text-center">ID</th>
                        <th class="text-center">Created_date</th>
                        <th class="text-center">Date</th>
                        <th class="text-center">Estimate Arrival</th>
                        <th class="text-center">Details</th>
                        <th class="text-center">Status</th>
                        <th class="text-center">Manage</th>
                    </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </x-card>
    @endif

    <x-modal.modal-full class="modal-shipment modal-hide">
        <x-modal.header>
            <x-modal.title>
                PO Shipment Details
            </x-modal.title>
            <x-modal.close-button id="closePOShipment" />
        </x-modal.header>
        <x-modal.body>
            <form method="POST" action="{{ route('update po_shipment') }}" id="form-edit-po-shipment" enctype="multipart/form-data"></form>
        </x-modal.body>
    </x-modal.modal-full>

    @push('bottom_js')

        <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/typeahead.js@0.11.1/dist/typeahead.bundle.min.js"></script>

        <script type="text/javascript">
            $(".datepicker-1").datepicker({
                dateFormat: 'dd-mm-yy'
            });

        </script>
        <script>
            const POShipmentTableURL = '{{ route('data_po_shipments') }}';
            $.fn.dataTable.ext.errMode = 'throw';
            var productTable = '';

            const loadPOShipmentTable = (url) => {
                productTable = $('#datatable').DataTable({
                    processing: true,
                    serverSide: true,
                    bDestroy: true,
                    "ajax": {
                        "url": url,
                        "dataSrc": function ( json ) {
                            for ( var i=0, ien=json.data.length ; i<ien ; i++ ) {
                                json.data[i][0] = json.data[i][0];
                            }

                            $(".all .order-purchase__filter-total").html(" ("+json.CountAll+")");
                            $(".open .order-purchase__filter-total").html(" ("+json.CountOpen+")");
                            $(".arrive .order-purchase__filter-total").html(" ("+json.CountArrive+")");
                            $(".closes .order-purchase__filter-total").html(" ("+json.CountClose+")");

                            return json.data;
                        }
                    },
                    columns: [
                        {
                            data: 'id',
                            searchable: false,
                        },
                        {
                            data: 'created_at'
                        },
                        {
                            data: 'date'
                        },
                        {
                            data: 'arrival_date'
                        },
                        {
                            data: 'details',
                            searchable: true,
                        },
                        {
                            data: 'status'
                        },
                        {
                            data: 'action',
                            searchable: false,
                            orderable: false
                        },
                    ],
                    columnDefs: [
                        {
                            "targets": [0, 1 ],
                            "visible": false
                        },
                        {
                            targets: [1, 2],
                            orderable: false,
                        },
                        {
                            targets: [2, 3,4],
                            className: 'text-center'
                        }
                    ],
                    order: [
                        [ 0, 'desc' ]
                    ],
                    paginationType: 'numbers',
                    initComplete: function(settings, json) {
                    }
                });
            }

            supplier_id = '';

            loadPOShipmentTable(`${POShipmentTableURL}`);

            $('#supplier_id').change(function() {
                $('#__purchaseOrderTable').dataTable().fnDestroy();

                supplier_id = $(this).val();
                status = $(".selected_status").data("status");
                if(typeof status === 'undefined') {
                    status = '';
                }
                loadPOShipmentTable(`${POShipmentTableURL}?status=${status}&&supplier_id=${supplier_id}`);
            });

            $('.btn-status-filter').click(function() {
                $('#__purchaseOrderTable').dataTable().fnDestroy();
                $('.btn-status-filter').removeClass('active');
                $('.btn-status-filter').removeClass('selected_status');
                $(this).addClass("active");
                $(this).addClass("selected_status");
                supplier_id = $('#supplier_id').val();
                if($('#supplier_id').val() === null){
                    supplier_id='';
                }
                status = $(this).data("status");

                loadPOShipmentTable(`${POShipmentTableURL}?status=${status}&&supplier_id=${supplier_id}`);

            });

            $(document).ready(function() {
                $('#__selectSupplierFilter').select2({
                    placeholder: '- Select Supplier -',
                    allowClear: true
                });

                $('#__selectCostFilter').select2({
                    placeholder: '- Select Lowest Sell Price  -',
                    allowClear: true
                });

                $('#__selectCostFilter').val('').trigger('change');
                $('#__selectSupplierFilter').val('').trigger('change');
            });

            const sortProductTable = sortBy => {
                switch (sortBy) {
                    case 'price_asc':
                        productTable.order([2, 'asc']).draw();
                        break;

                    case 'price_desc':
                        productTable.order([2, 'desc']).draw();
                        break;

                    default:
                        productTable.order([2, 'asc']).draw();
                        break;
                }
            }

            $(document).on('keyup', '.ship_quantity', function(event) {

                let ship_quantity = parseInt($(this).val());
                let old_qty = $(this).data('old-qty');
                let product_code = $(this).data('code');
                let pieces_per_pack = parseInt($("#pieces_per_pack_"+product_code).html());

                let pieces  = ship_quantity*pieces_per_pack;

                $(this).next().find(".res_pieces").html(pieces);
                //console.log(pieces + ">>"+ pack);
                let order_qty = $("#order_qty_"+product_code).html();
                let shipped_qty = parseInt($("#shipped_qty_"+product_code).html())+parseInt(ship_quantity) - parseInt(old_qty);
                let available_qty = parseInt(order_qty) - parseInt(shipped_qty)  ;

                $("#available_qty_"+product_code).html(available_qty);
                $(this).next().find(".res_pieces").html(pieces);
            });


            // This will load Shipment Form to Edit
           $(document).on('click', '.__BtnEditShipment', function() {
                var id = $(this).attr('data-id');
                var editShippingDetails = [];
                    $.ajax({
                        url: '{{ route('edit_po_shipment') }}',
                        type: 'post',
                        data: {
                            'id': $(this).data('id'),
                            '_token': $('meta[name=csrf-token]').attr('content')
                        }
                    }).done(function(result) {

                        editShippingDetails = result;

                        // Edit Shipment Form
				$('#add_shipment_wrapper_new').removeClass("hide");


                $(".modal-shipment #LoadShipmentForm").html(" ");

				$('#add_shipment_wrapper_new').removeClass("hide");
				var templateShipmentEditForm = $('#add_shipment_wrapper_new').clone();

                templateShipmentEditForm.html(function(index, html) {
                       return html.replace('{id}', editShippingDetails.id);
                });

                templateShipmentEditForm.html(function(index, html) {
                       return html.replace('{factory_tracking}', editShippingDetails.factory_tracking);
                });

                templateShipmentEditForm.html(function(index, html) {
                       return html.replace('{number_of_cartons}', editShippingDetails.number_of_cartons);
                });

                templateShipmentEditForm.html(function(index, html) {
                       return html.replace('{ship_date}', editShippingDetails.ship_date);
                });

                templateShipmentEditForm.html(function(index, html) {
                       return html.replace('{e_a_d_f}', editShippingDetails.e_a_d_f ? editShippingDetails.e_a_d_f : null);
                });

                templateShipmentEditForm.html(function(index, html) {
                       return html.replace('{e_a_d_t}', editShippingDetails.e_a_d_t);
                });


                templateShipmentEditForm.html(function(index, html) {
                    return html.replace('{selected_'+editShippingDetails.status+'}', "selected");
                });

                templateShipmentEditForm.html(function(index, html) {
                        return html.replace('{selected_'+editShippingDetails.domestic_shipper_id+'}','selected');
                });

                templateShipmentEditForm.html(function(index, html) {
                       return html.replace('{cargo_ref}', editShippingDetails.cargo_ref);
                });

                templateShipmentEditForm.html(function(index, html) {
                       return html.replace('{domestic_logistics}', editShippingDetails.domestic_logistics);
                });

                templateShipmentEditForm.html(function(index, html) {
                       return html.replace('{is_edit}', true);
                });

                templateShipmentEditForm.html(function(index, html) {
                    var label_shipping_type = " ";
                    if($("#agent_cargo_id option:selected" ).val() > 0){
                        label_shipping_type = $("#agent_cargo_id option:selected" ).text();
                    }
                    return html.replace('{label_shipping_type}', label_shipping_type);
                });

                templateShipmentEditForm.html(function(index, html) {
                    var shipping_mark_text = " ";
                    if($("#shipping_mark_id option:selected" ).val() > 0){
                        shipping_mark_text = $("#shipping_mark_id option:selected" ).text();
                    }

                    return html.replace('{label_shipping_mark}', shipping_mark_text);
                });


				// This will fix error and run the datetpicker into modal form
				templateShipmentEditForm.find('input.datepicker-1').removeClass('hasDatepicker').datepicker({
					dateFormat: 'dd-mm-yy'
				});

			    $(".modal-shipment #LoadShipmentForm").html(templateShipmentEditForm);

                var shipmentDetails = editShippingDetails.po_shipment_details;

				renderProductToShippingForm(selectedProductsToList.join(','),shipmentDetails);

				$('.modal-shipment').removeClass('modal-hide');
				$('body').addClass('modal-open');



                });


            });

            $(document).on('click', '.BtnEditShipment', function() {

                var id = $(this).attr('data-id');
                var order_purchase_id = $(this).attr('data-order_purchase_id');
                $.ajax({
                    url: '{{ route('single edit po_shipment form') }}?id=' + id+'&order_purchase_id=' + order_purchase_id,
                    beforeSend: function() {
                        $('#form-edit-po-shipment').html('Loading');
                    }
                }).done(function(result) {
                    console.log(result);
                    $('#form-edit-po-shipment').html(result);

                    $('.modal-shipment').removeClass('modal-hide');
                    $('body').addClass('modal-open');
                });
            });

            $(document).on('click', '.BtnDelete', function() {
                let drop = confirm('Are you sure?');
                if (drop) {
                    //remove from data table
                    $(this).closest('tr').remove();

                    $.ajax({
                        url: '{{ route('delete po_shipment') }}',
                        type: 'post',
                        data: {
                            'id': $(this).data('id'),
                            '_token': $('meta[name=csrf-token]').attr('content')
                        }
                    }).done(function(result) {
                        if (result.status === 1) {
                            alert('Data deleted successfully');
                            setTimeout(() => {
                                //window.location.href = '{{ route('po_shipments') }}';
                            }, 200);

                        } else {
                            alert(result.message);
                        }
                    });
                }
            });

            const removeProductItem = el => {
                const productCode = el.getAttribute('data-code');

                $(`.__row_ProductItem_${productCode}`).remove();

                if (selectedProductsToList.length === 0) {
                    $('#__wrapper_NoProduct').show();
                }
            }

            $('#closePOShipment').click(function() {
                $('.modal-shipment').addClass('modal-hide');
                $('body').removeClass('modal-open');
            });
        </script>
    @endpush

</x-app-layout>
