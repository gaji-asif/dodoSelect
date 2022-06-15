<x-app-layout>
    @section('title')
    {{ __('translation.Order Manage') }}
    @endsection

    @push('top_css')
    <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <link rel="stylesheet" href="{{ asset('css/custom_css.css') }}">
    @endpush
    <style type="text/css">
        #yajra_datatable tbody tr td{
           text-align: left;
       }
       .sorting_1{
           text-align: left;
       }
       .btn-status-filter{
        float: left;
        margin-right: 10px;
        margin-bottom: 8px;
    }
</style>
<input type="hidden" name="" id="shipment_for" value="{{$shipment_for}}">
@include('seller.shipments.shipments_tab')
@if(\App\Models\Role::checkRolePermissions('Can access menu: All Shipment'))
<div class="col-span-12 margin_top__35">
    <div class="flex flex-row items-center justify-between mb-2">
        <h2 class="block whitespace-nowrap text-yellow-500 text-base font-bold">
            {{__('translation.Shipments')}}
        </h2>
        <hr class="w-full ml-3 relative -top-1 border border-r-0 border-b-0 border-l-0 border-yellow-300">
    </div>
    <div class="mb-2 flex flex-row items-center justify-between">
        <div class="sm:w-full">
            <x-input type="text" id="shipment_no" name="shipment_no" placeholder="Search by Shipment No" autocomplete="off" />
        </div>
        <div class="w-auto mx-4 lg:mx-6 sm:w-1/6 lg:w-auto text-center">
         <x-button type="button" color="blue" id="__search" class="h-10 relative top-[0.10rem] sm:-top-1 lg:w-full mt-1" title="{{ __('translation.Find By Grid') }}">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path>
            </svg>
            <span class="whitespace-nowrap hidden sm:block sm:ml-2">
                {{ __('translation.Search') }}
            </span>
        </x-button>
    </div>
    <div class="w-auto sm:w-2/5 lg:w-1/4 xl:w-1/4">
        <div class="flex items-center justify-center sm:justify-end sm:relative">
           <x-select name="shipment_status" id="shipment_status">
            <option value="">Filter by Status</option>
            @if($shipment_for == \App\Models\Shipment::SHIPMENT_FOR_DODO)
            <option value="10">{{ __('translation.Wanting for Stock') }}</option>
            <option value="11">{{ __('translation.Ready To ship') }}</option>
            <option value="12">{{ __('translation.Shipped') }}</option>
            <option value="13">{{ __('translation.Cancelled') }}</option>
            <option value="14">{{ __('translation.Ready To ship (Printed)') }}</option>
            @endif
            @if($shipment_for == \App\Models\Shipment::SHIPMENT_FOR_WOO)
            <option value="{{\App\Models\Shipment::SHIPMENT_STATUS_WOO_HOLD}}">{{ __('translation.Hold') }}</option>
            <option value="{{\App\Models\Shipment::SHIPMENT_STATUS_WOO_READY_TO_SHIP}}">{{ __('translation.Ready To ship') }}</option>
            <option value="{{\App\Models\Shipment::SHIPMENT_STATUS_WOO_PROCESSING}}">{{ __('translation.Processing') }}</option>
            <option value="{{\App\Models\Shipment::SHIPMENT_STATUS_WOO_PENDING}}">{{ __('translation.Pending') }}</option>
            <option value="{{\App\Models\Shipment::SHIPMENT_STATUS_WOO_COMPLETED}}">{{ __('translation.Completed') }}</option>
            <option value="{{\App\Models\Shipment::SHIPMENT_STATUS_WOO_CANCEL}}">{{ __('translation.Cancelled') }}</option>
            @endif
        </x-select>
    </div>
</div>
</div>
<div class="col-lg-12 mb-3">
    <div class="row">
    <div class="" id="error_found_message"></div>
    <input type="hidden" name="" id="order_id">
</div>
</div>
<div class="">
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
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

                    <div class="row">

                    <div class="col-lg-10 col-xs-12 display_none date_wise_filter_div">
                        <x-page.order-purchase.card-filter label="All Shipments" data-status="all" class="btn-status-filter active all">
                        {{$shipmentsTotalAll}}
                        </x-page.order-purchase.card-filter>

                        <x-page.order-purchase.card-filter label="Today" data-status="today" class="btn-status-filter today">
                        {{$shipmentsTotalToday}}
                        </x-page.order-purchase.card-filter>

                        <x-page.order-purchase.card-filter label="Late" data-status="late" class="btn-status-filter late">
                        {{$shipmentsTotalBeforeToday}}
                    </x-page.order-purchase.card-filter>

                 </div>
            <div class="col-lg-2 col-xs-12 col-sm-12">
                <div class="row text-center">
                <a id="bulk_print" class="btn btn-success rest_btn_wrapper bulk_print_shipment margin_left_12 text-center">
                    <span class="ml-2">
                        <i class="fa fa-print mr-1" aria-hidden="true"></i>
                        Bulk Print
                    </span>
                </a>
              </div>
            </div>

    </div>

</div>
<div class="flex justify-between flex-col col-lg-12">
    <div class="overflow-x-auto">
        <table class="table-auto border-collapse w-100  border mt-4" id="yajra_datatable">
            <thead class="border bg-green-300">
                <tr class="rounded-lg text-sm font-medium text-gray-700 text-left">
                    <th class="px-4 py-2 text-left"></th>
                    <!-- <th class="px-4 py-2 text-center">Shipment Id</th> -->
                    <th width="30%" class="px-4 py-2 text-center">Details</th>
                  </tr>
                    </thead>
                    <tbody>

                    </tbody>
                </table>
                </div>
            </div>
        </div>
    </div>
</div>
</div>
</div>
</div>
    @endif

    <div class="modal" tabindex="-1" role="dialog" id="print_level_modal">
        <div class="modal-dialog" role="document">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title">
                        <strong>{{__('translation.Create Print Label')}}</strong>
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="mb-4">
                        <button type="button" class="btn btn-success" id="customers_details_btn">Customer details</button>
                        <button type="button" class="btn btn-warning" id="order_details_btn">Shipment Product details</button>
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

                     <form method="POST" action="{{route('printLevelPrint')}}" enctype="multipart/form-data">
                       @csrf
                       <input type="hidden" id="shipment_id_input_val" name="shipment_id_input_val">
                       <input type="hidden" id="order_id_input_val" name="order_id_input_val">
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
                    <strong> {{__('translation.Create Pick Confirm')}} </strong>
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

                     <input class="btn btn-success" style="margin: 0 auto; padding: 5px 10px;" type="button" onclick="confirmPacking()" value="{{__('translation.Confirm Pick')}}" />


                     <form method="POST" action="{{route('printLevelPrint')}}" enctype="multipart/form-data">
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

<div class="modal" tabindex="-1" role="dialog" id="delete_shipment_modal">
    <div class="modal-dialog" role="document">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">
                    <strong>Delete this Shipment </strong>
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">

                <div id="printableArea_pack">
                    <h6 class="order_shipment_color">
                        <strong id="order_id_div_delete"></strong>
                        <strong style="float: right;" id="shipment_id_div_delete"></strong>
                    </h6>
                    <div class="" id="order_details_delete"></div>

                    <div class="mt-4 text-center">

                     <input class="btn btn-danger" style="margin: 0 auto; padding: 5px 10px;" type="button" onclick="deleteShipment()" value="Delete" />


                     <form method="POST" action="{{route('printLevelPrint')}}" enctype="multipart/form-data">
                       @csrf
                       <input type="hidden" id="shipment_id_input_val_delete" name="shipment_id_input_val_delete">
                       <input type="hidden" id="order_id_input_val_delete" name="order_id_input_val_delete">

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

<div class="modal" tabindex="-1" role="dialog" id="bulk_print_modal">
    <div class="modal-dialog" role="document">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">
                    <strong>Bulk Print</strong>
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
               <div class="" id="selected_item"></div>

               <div class="mt-4 text-center">
                <form method="POST" action="{{route('printLevelBulk')}}" enctype="multipart/form-data">
                   @csrf
                   <input type="hidden" id="shipment_ids_input_array" name="shipment_ids_input_array">

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

<x-modal.modal-small class="modal-hide" id="__modalMarkAsShipped">
            <x-modal.header>
                <x-modal.title>
                    {{ __('translation.Confirm') }}
                </x-modal.title>
            </x-modal.header>
            <x-modal.body>
                <div class="mb-5">
                    <p class="text-center">
                        {{ __('translation.Are your Sure Your want to confirm Shipment Status?') }}
                    </p>
                    <input type="hidden" id="order_id_value_MarkAsShipped">
                    <input type="hidden" id="shipment_id_value_MarkAsShipped">
                </div>
                <div class="text-center pb-5">
                    <x-button type="button" color="gray" id="__btnCloseModalCancelMarkAsShipped">
                        {{ __('translation.No, Close') }}
                    </x-button>
                    <x-button-link color="red" id="__btnCloseModalFinalMarkAsShipped">
                        {{ __('translation.Yes, Continue') }}
                    </x-button-link>
                </div>
            </x-modal.body>
        </x-modal.modal-small>
    <div class="modal" tabindex="-1" role="dialog" id="__modalAfterSearchShipmentID">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title width_100">
                    <strong class="order_shipment_id" id="order_shipment_id"></strong>
                    <font class="pull-right float_right color-green font_family_custom after_search_status">STATUS: <strong id="shipment_status_div"></strong></font>
                </h5>
                <button type="button" class="close" onclick="clearShipmentNo()" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
               <div class="mb-5 font-size-15">
                    Customer Name : <strong id="customer_id_div"></strong><br>
                    <!-- Total Items : <strong id="total_items_div"></strong> -->
                    <input type="hidden" id="order_id_value_after_search">
                    <input type="hidden" id="shipment_id_value_after_search">
                </div>
                <div id="after_search_modal_content"></div>
            </div>
    </div>
</div>
</div>

<div class="modal" tabindex="-1" role="dialog" id="pickOrderCancel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <strong>{{ __('translation.Cancel') }}</strong>
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
               <div class="mb-5">
                    <p class="text-center">
                        <strong>{{ __('translation.Are you sure you want to cancel this?') }}</strong>
                    </p>
                    <input type="hidden" id="order_id_value_for_cancel_pick_order">
                    <input type="hidden" id="shipment_id_for_cancel_pick_order">
                </div>
                <div class="text-center pb-5">

                    <x-button-link color="red" id="__btnCloseModalpickOrderCancel">
                        {{ __('translation.Yes, Continue') }}
                    </x-button-link>
                </div>
            </div>
    </div>
</div>
</div>

<div class="modal" tabindex="-1" role="dialog" id="markAsShippedUpdateModal">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <strong>{{ __('translation.Update Status') }}</strong>
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
               <div class="mb-5">
                    <p class="text-center">
                        <strong>{{ __('translation.Select any from these status for update?') }}</strong>
                    </p>
                    <input type="hidden" id="order_id_value_for_mark_as_shipped_update">
                    <input type="hidden" id="shipment_id_for_mark_as_shipped_update">
                </div>
                <div class="text-center pb-5">
                    <div class="width_60">
                    <x-select class="margin_bottom_30" name="shipment_status_update" id="shipment_status_update">
                        <option value="">Filter by Status</option>
                        @if($shipment_for == \App\Models\Shipment::SHIPMENT_FOR_DODO)
                        <option value="10">{{ __('translation.Wanting for Stock') }}</option>
                        <option value="11">{{ __('translation.Ready To ship') }}</option>
                        <option value="12">{{ __('translation.Shipped') }}</option>
                        <option value="13">{{ __('translation.Cancelled') }}</option>
                        <option value="14">{{ __('translation.Ready To ship (Printed)') }}</option>
                        @endif
                        @if($shipment_for == \App\Models\Shipment::SHIPMENT_FOR_WOO)
                        <option value="{{\App\Models\Shipment::SHIPMENT_STATUS_WOO_HOLD}}">{{ __('translation.Hold') }}</option>
                        <option value="{{\App\Models\Shipment::SHIPMENT_STATUS_WOO_READY_TO_SHIP}}">{{ __('translation.Ready To ship') }}</option>
                        <option value="{{\App\Models\Shipment::SHIPMENT_STATUS_WOO_PROCESSING}}">{{ __('translation.Processing') }}</option>
                        <option value="{{\App\Models\Shipment::SHIPMENT_STATUS_WOO_PENDING}}">{{ __('translation.Pending') }}</option>
                        <option value="{{\App\Models\Shipment::SHIPMENT_STATUS_WOO_COMPLETED}}">{{ __('translation.Completed') }}</option>
                        <option value="{{\App\Models\Shipment::SHIPMENT_STATUS_WOO_CANCEL}}">{{ __('translation.Cancelled') }}</option>
                        @endif
                    </x-select>
                   </div>
                    <x-button-link color="red" id="__btnCloseModalMarkAsShippedUpdate">
                        {{ __('translation.Yes, Continue') }}
                    </x-button-link>
                </div>
            </div>
    </div>
</div>
</div>

    <x-modal.modal-large id="__modalViewCustomShipmentForOrder" class="modal-hide">
        <x-modal.header>
            <x-modal.title>
                {{__('translation. View shipped Details')}}
            </x-modal.title>
        </x-modal.header>
        <x-modal.body>
            <x-alert-danger id="__alertDangerViewCustomShipmentForOrder" class="alert mb-5 hidden">
                <div id="__alertDangerContentViewCustomShipmentForOrder"></div>
            </x-alert-danger>

           <div id="modal_content_view_custom_shipment_for_order"></div>
        </x-modal.body>
    </x-modal.modal-large>
@push('bottom_js')
<script src="{{asset('js/jquery.validate.js')}}"></script>
<script src="{{asset('js/dataTables.checkboxes.min.js')}}"></script>
<script>

        function confirmPacking(){

            //var chk_arr = $('input[name="chekecked_product_id[]"]:checked').length;
            var total = $("#total_count").val();
            var shipment_id = $("#shipment_id_input_val_pack").val();
            var order_id = $("#order_id_input_val_pack").val();
                $.ajax
                ({
                    type: 'POST',
                    data: {shipment_id:shipment_id, order_id:order_id},
                    url: '{{url('updateShipmentStatus')}}',
                    success: function(result)
                    {
                        // alert(result);
                        if(result === 'ok'){
                            $("#pack_order_modal").modal('hide');
                            alert("Your Order has been successfully packed");
                            var url = "{{ url('/all_shipment/') }}";
                            window.location.href = url+'/'+$("#shipment_for").val();
                        }

                    }
                });
            }


        function deleteShipment(){

            var pack_status = $("#pack_status").val();

            if(pack_status === '1'){

                var chk_arr = $('input[name="chekecked_product_id[]"]:checked').length;
                    // $('input:checkbox:not(":checked")').length;
                    var total = $("#total_count_for_del").val();

                    if(Number(chk_arr) != Number(total)){
                    alert("Please checked all the product from here");

                }
                else{

                    var shipment_id = $("#shipment_id_input_val_delete").val();
                    var order_id = $("#order_id_input_val_delete").val();
                    $.ajax
                    ({
                        type: 'POST',
                        data: {shipment_id:shipment_id, order_id:order_id},
                        url: '{{url('deleteShipment')}}',
                        success: function(result)
                        {
                        // alert(result);
                        if(result === 'ok'){
                            $("#delete_shipment_modal").modal('hide');
                            alert("Your Shipment has been successfully Deleted");
                            // dataTables("{{ route('all_shipment_list') }}");
                            window.location.href = "{{ url('all_shipment')}}";
                        }

                    }
                });

                }
            }
            if(pack_status === '0'){
               var shipment_id = $("#shipment_id_input_val_delete").val();
               var order_id = $("#order_id_input_val_delete").val();
               $.ajax
               ({
                type: 'POST',
                data: {shipment_id:shipment_id, order_id:order_id},
                url: '{{url('deleteShipment')}}',
                success: function(result)
                {
                        // alert(result);
                        if(result === 'ok'){
                            $("#delete_shipment_modal").modal('hide');
                            alert("Your Shipment has been successfully Deleted");
                            // dataTables("{{ route('all_shipment_list') }}");
                            window.location.href = "{{ url('all_shipment')}}";
                        }

                    }
                });
           }

       }


       $(document).ready(function() {
        let shipment_for = $("#shipment_for").val();
        dataTables("{{ route('all_shipment_list') }}?shipment_for=" + shipment_for);
        var datatable;

        $('.btn_processing').click(function() {
            datatable.destroy();
            var status = 'open';
            dataTables("{{ route('all_shipment_list') }}?status=" + status);
            $(".btn_process_order").removeClass('hide');
        });

        $('.btn-status-filter').click(function() {
            datatable.destroy();
            $('.btn-status-filter').removeClass('active');
            $(this).addClass("active");
            var status = $(this).data("status");
            dataTables("{{ route('all_shipment_list') }}?status=" + status);
        });


        $(document).on('click', '#__search', function() {
            $("#error_found_message").hide();
            let shipment_no = $("#shipment_no").val();
            let shipment_for = $("#shipment_for").val();
            datatable.destroy();
            dataTables("{{ route('all_shipment_list') }}?shipment_no=" + shipment_no+"&shipment_for=" + shipment_for);
        });

        $(document).on('change', '#shipment_status', function() {
            datatable.destroy();
            var shipment_status = $("#shipment_status").val();
            let shipment_for = $("#shipment_for").val();
            if(shipment_status === '11'){
               $(".date_wise_filter_div").show();
            }
            else{
               $(".date_wise_filter_div").hide();
            }

            dataTables("{{ route('all_shipment_list') }}?shipment_status=" + shipment_status+"&shipment_for=" + shipment_for);
        });


        function dataTables(url) {
                    // Datatable
                    datatable = $('#yajra_datatable').DataTable({
                        processing: true,
                        // responsive: true,
                        serverSide: true,
                        columnDefs : [
                        {
                            'targets': 0,
                            'checkboxes': {
                                'selectRow': true
                            }
                        }
                        ],
                        order: [[1, 'desc']],
                        ajax: url,
                        columns: [
                        {
                            name: 'checkbox',
                            data: 'checkbox'
                        },

                        {
                            data: 'details',
                            name: 'details',
                            orderable: true,
                            searchable: true
                        },
                        ]
                        });
                }

                $(document).on('change', '#bulk_action', function() {
                    var status = $(this).val();
                    var rows_selected = datatable.column(0).checkboxes.selected();

                    var arr = [];
                    $.each(rows_selected, function(index, rowId){
                        arr[index]=rowId;
                        datatable.cell(index,8).data(status);
                    });

                    if(arr.length === 0){
                        alert("Please Select Order ID");
                        return;
                    }

                    if(status === '3'){
                        alert("You can not change order to ready to Ship Manually");
                        datatable.destroy();
                        dataTables("{{ route('ordersList') }}");
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
                            //$('#messageStatus').html("Please wait...");
                        }
                    }).done(function(result) {
                        alert(result);
                        datatable.destroy();
                        dataTables("{{ route('ordersList') }}");
                        // console.log(result);
                    });
                });
            });

$(document).on('click', '#bulk_print', function() {

    var rows_selected = $('#yajra_datatable').DataTable().column(0).checkboxes.selected();

    var arr = [];
    $.each(rows_selected, function(index, rowId){
        arr[index]=rowId;
                    //datatable.cell(index,8).data(status);
                });

    console.log(arr);

    if(arr.length === 0){
        alert("Please Select Shipment ID First");
        return;
    }
    $( '#shipment_ids_input_array' ).val( arr );
    $("#bulk_print_modal").modal('show');

    $("#selected_item").text('You have selected total '+arr.length+' shipments.');
});

    $(document).on('click', '#BtnUpdate', function() {
        var order_id = $(this).attr('data-id');
        var url = "{{ url('/order_management/') }}";
        window.location.href = url+'/'+order_id+'/edit';

    });

    function printLevel(shipment_id, order_id){
    $("#pack_order_modal").modal('hide');
    $("#print_level_modal").modal('show');
    $("#shipment_id_input_val").val(shipment_id);
    $("#order_id_input_val").val(order_id);
    $("#order_id_div").text('Order ID #'+order_id);
    $("#shipment_id_div").text('Shipment ID #'+shipment_id);
    $.ajax
    ({
        type: 'GET',
        data: {shipment_id:shipment_id, order_id:order_id},
        url: '{{url('getCustomerOrderHistory')}}',
        success: function(result)
        {
            console.log(result);
            $("#order_details").html(result);
        }
    });
}

function packOrder(shipment_id, order_id){
 $("#pack_order_modal").modal('show');
    $("#print_level_modal").modal('hide');
    $("#shipment_id_input_val_pack").val(shipment_id);
    $("#order_id_input_val_pack").val(order_id);
    $("#order_id_div_pack").text('Order ID #'+order_id);
    $("#shipment_id_div_pack").text('Shipment ID #'+shipment_id);
    $.ajax
    ({
        type: 'GET',
        data: {shipment_id:shipment_id, order_id:order_id},
        url: '{{url('getCustomerOrderHistoryForPack')}}',
        success: function(result)
        {
                //alert(result);
                console.log(result);
                $("#order_details_pack").html('');
                $("#order_details_pack").html(result);
                clearShipmentNo();
            }
        });
}

// $(document).on('click', '#packOrder', function() {

// });

$(document).on('click', '#deleteShipment', function() {
    $("#delete_shipment_modal").modal('show');
    $("#print_level_modal").modal('hide');
    var shipment_id = $(this).data('id');
    var order_id = $(this).attr('order-id');
    $("#shipment_id_input_val_delete").val(shipment_id);
    $("#order_id_input_val_delete").val(order_id);
    $("#order_id_div_delete").text('Order ID #'+order_id);
    $("#shipment_id_div_delete").text('Shipment ID #'+shipment_id);
    $.ajax
    ({
        type: 'GET',
        data: {shipment_id:shipment_id, order_id:order_id},
        url: '{{url('getCustomerOrderHistoryForDelete')}}',
        success: function(result)
        {
            //alert(result);
            console.log(result);
            $("#order_details_delete").html('');
            $("#order_details_delete").html(result);
        }
    });
});

$(".datepicker-1").datepicker({
                //dateFormat: 'dd-mm-yy'
                dateFormat: 'yy-mm-dd',
            });
        </script>

        <script type="text/javascript">

          function printView(){
            $("#print_div").show();
            $("#print_level_modal_print").modal('show');
            var shipment_id = $("#shipment_id_input_val").val();
            var order_id = $("#order_id_input_val").val();
            $("#order_id_div_print").text('Order ID #'+order_id);
            $("#shipment_id_div_print").text('Shipment ID #'+shipment_id);
        }

        function printDiv(divName) {
         $(".customers_details_div").hide();
         $(".order_details_div").hide();
         var shipment_id = $("#shipment_id_input_val").val();
         var order_id = $("#order_id_input_val").val();
         $(".order_id_div_print").text('Order ID #'+order_id);
         $(".shipment_id_div_print").text('Shipment ID #'+shipment_id);

         $("#print_div").show();

         var printContents = document.getElementById('print_div').innerHTML;

         var originalContents = document.body.innerHTML;
             //let document = window.open('', 'PRINT', 'height=650,width=900,top=100,left=150');
             document.body.innerHTML = printContents;
             window.print();
             document.body.innerHTML = originalContents;
             window.close();

             $(".customers_details_div").show();
             $(".order_details_div").show();
             $("#print_div").hide();
             window.location.href = "{{ url('all_shipment')}}";

             $("#print_level_modal").modal('hide');
              // setTimeout(function(){window.close()}, 3000);
              //printDiv1();
          }

          function markAsShipped(shipment_id, order_id){
              $("#shipment_id_value_MarkAsShipped").val(shipment_id);
              $("#order_id_value_MarkAsShipped").val(order_id);
              $('#__modalMarkAsShipped').doModal('open');
              $('#__btnCloseModalCancelMarkAsShipped').removeClass('hidden');
              clearShipmentNo();
          }

         $('#__btnCloseModalCancelMarkAsShipped').on('click', function() {
            $('#__modalMarkAsShipped').doModal('close');
            $('#__btnCloseModalCancelMarkAsShipped').addClass('hidden');
            clearShipmentNo();
         });



         $('#__btnCloseModalFinalMarkAsShipped').on('click', function() {
         clearShipmentNo();
         var orderId = $("#order_id_value_MarkAsShipped").val();
         var shipment_id = $("#shipment_id_value_MarkAsShipped").val();
         $.ajax({
                type: 'GET',
                url: '{{url('markAsShipped')}}',
                data: {orderId:orderId, shipment_id:shipment_id},
                beforeSend: function() {
                    $("#__btnCloseModalFinalMarkAsShipped").html("Processing...");
                },
                success: function(responseData) {
                    $('#__modalCancelCustomShipment').doModal('close');
                    var url = "{{ url('/all_shipment/') }}";
                    window.location.href = url+'/'+$("#shipment_for").val();
                    console.log(responseData);
              },
              error: function(error) {

              }
            });
        });

         $("#shipment_no").keypress(function(e) {
            if(e.which == 13) {
            let shipment_id = $("#shipment_no").val();
            let shipment_for = $("#shipment_for").val();

            $.ajax({
                type: 'GET',
                url: '{{url('checkIfExistsShipmentId')}}',
                data: {shipment_id:shipment_id, shipment_for:shipment_for},
                beforeSend: function() {
                    //$("#__btnCloseModalFinalMarkAsShipped").html("Processing...");
                },
                success: function(responseData) {
                // alert(responseData.data);
                 console.log(responseData.customer_name);

                   if(responseData.order_id){
                    var order_id = responseData.order_id;
                    var url = "{{ url('/order_management/') }}";
                    var edit_order_redirect_url = url+'/'+responseData.order_id+'/edit';
                    $("#order_id").val(responseData.order_id);
                    $('#__modalAfterSearchShipmentID').modal('show');
                    $("#shipment_id_value_after_search").val(shipment_id);
                    $("#order_shipment_id").html('<a href="'+edit_order_redirect_url+'"><strong class="text_underline_asif color-blue">#'+responseData.order_id+'</strong></a> (Ship ID: <strong>'+shipment_id+')</strong> ');
                    $("#customer_id_div").html(responseData.customer_name);
                    $("#total_items_div").html(responseData.getTotalItems);
                    $("#shipment_status_div").html(responseData.shipment_status);
                    $("#error_found_message").hide();

                         $.ajax({
                            type: 'GET',
                            url: '{{url('after_search_modal_content')}}',
                            data: {shipment_id:shipment_id, order_id:order_id},
                            beforeSend: function() {
                                $("#after_search_modal_content").html("Loading...");
                            },
                            success: function(responseDataNew) {
                                //$("#after_search_modal_content").html("");
                                console.log(responseDataNew);
                                $("#after_search_modal_content").html(responseDataNew);


                            },
                            error: function(error) {

                            }
                        });
                   }
                   else{
                    $("#error_found_message").show();
                    $("#error_found_message").html(responseData);
                   }
              },
              error: function(error) {

              }
            });

            }
         });

         $('#__btnCloseModalCancelAfterSearch').on('click', function() {
            clearShipmentNo();
            $('#__modalAfterSearchShipmentID').doModal('close');
            $('#__btnCloseModalFinalAfterSearch').addClass('hidden');
         });


         $('#printLabelBtnAfterSearch').on('click', function() {
            clearShipmentNo();
            var order_id = $("#order_id").val();
            var shipment_id =  $("#shipment_id_value_after_search").val();
            $('#__modalAfterSearchShipmentID').modal('hide');
            printLevel(shipment_id, order_id);
         });

         $('#pickConfirmBtnAfterSearch').on('click', function() {
            clearShipmentNo();
            var order_id = $("#order_id").val();
            var shipment_id =  $("#shipment_id_value_after_search").val();
            $('#__modalAfterSearchShipmentID').modal('hide');
            packOrder(shipment_id, order_id);
         });

          $('#markAsShippedBtnAfterSearch').on('click', function() {
            clearShipmentNo();
            var order_id = $("#order_id").val();
            var shipment_id =  $("#shipment_id_value_after_search").val();
            $('#__modalAfterSearchShipmentID').modal('hide');
            markAsShipped(shipment_id, order_id);
         });

        function see_total_items(shipment_id, order_id){
         var use_for = "view";
         $('#__modalViewCustomShipmentForOrder').doModal('open');
            $.ajax({
                type: 'GET',
                url: '{{url('getModalContentForEditCustomShipment')}}',
                data: {orderId:order_id, shipment_id:shipment_id, use_for:use_for},
                beforeSend: function() {
                    $("#modal_content_view_custom_shipment_for_order").html("Loading...");
                },
                success: function(responseData) {
                    $("#modal_content_view_custom_shipment_for_order").html("");
                    console.log(responseData);
                    $("#modal_content_view_custom_shipment_for_order").html(responseData);


                },
                error: function(error) {

                }
            });
         }

     function pickOrderCancel(shipment_id, order_id){
        clearShipmentNo();
        $("#shipment_id_for_cancel_pick_order").val(shipment_id);
        $("#order_id_value_for_cancel_pick_order").val(order_id);
        $('#pickOrderCancel').modal('show');
     }


     $('#__btnCloseModalpickOrderCancel').on('click', function() {
        clearShipmentNo();
        var shipment_id = $("#shipment_id_for_cancel_pick_order").val();
        var order_id = $("#order_id_value_for_cancel_pick_order").val();
        $.ajax({
            type: 'GET',
            url: '{{url('shipmentPickOrderCancel')}}',
            data: {shipment_id:shipment_id},
            beforeSend: function() {
                //$("#__btnCloseModalFinalMarkAsShipped").html("Processing...");
            },
            success: function(responseData) {
                $("#pickOrderCancel").modal('hide');
                var url = "{{ url('/all_shipment/') }}";
                window.location.href = url+'/'+$("#shipment_for").val();
            },
          error: function(error) {

          }
        });
     });

     function markAsShippedUpdate(shipment_id, order_id){
        clearShipmentNo();
        $("#shipment_id_for_mark_as_shipped_update").val(shipment_id);
        $("#order_id_value_for_mark_as_shipped_update").val(order_id);
        $('#markAsShippedUpdateModal').modal('show');
     }


     $('#__btnCloseModalMarkAsShippedUpdate').on('click', function() {
        clearShipmentNo();
        var shipment_id = $("#shipment_id_for_mark_as_shipped_update").val();
        var order_id = $("#order_id_value_for_mark_as_shipped_update").val();
        var shipment_status_update = $("#shipment_status_update").val();
        $.ajax({
            type: 'GET',
            url: '{{url('shipment_status_update')}}',
            data: {shipment_id:shipment_id, shipment_status_update:shipment_status_update},
            beforeSend: function() {
                //$("#__btnCloseModalFinalMarkAsShipped").html("Processing...");
            },
            success: function(responseData) {
                $("#markAsShippedUpdateModal").modal('hide');
                var url = "{{ url('/all_shipment/') }}";
                window.location.href = url+'/'+$("#shipment_for").val();
            },
          error: function(error) {

          }
        });
     });

     function clearShipmentNo(){
            $('input[name=shipment_no').val('');
     }

    </script>
    @endpush

    </x-app-layout>
