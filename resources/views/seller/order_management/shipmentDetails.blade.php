<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.19/dist/sweetalert2.min.css">
<x-card.header>
    <x-card.back-button href="{{ route('order_management.index') }}" id="left_pad" />
    <x-card.title>
        Shipment Details #{{$order_id}}
    </x-card.title>
</x-card.header>

<div class="col-12 tabs">
    <div class="col-lg-12 mt-4">
        <x-section.title>
            Shipment
        </x-section.title>
        <h6 class="mt-4"><strong>Shipment Products : </strong></h6>
        <div class="w-full overflow-x-auto">
            <table class="table text-center tbl_border" id="shipments_table">
                <thead>
                <tr class="bg-blue-500 text-white align-self-sm-baseline">
                    <th>{{ __('translation.Product Name') }}</th>
                    <th>{{ __('translation.Ordered') }}</th>
                    <th>{{ __('translation.Quantity') }}</th>
                    <th>{{ __('translation.Remaining') }}</th>
                </tr>
                </thead>
                <tbody class="bg-white">
                @if(isset($getAllOredredDetails))
                    @foreach($getAllOredredDetails as $value)
                        <tr>
                            <td class="text-left">
                                <div>
                                    <span>{{$value->product_name}}</span> <br>
                                    <span class="text-blue-500">{{$value->product_code}}</span>
                                </div>
                            </td>
                            <td>{{$value->ordered_qty}}</td>
                            @php
                                $getAllQtyByReadyToShip = \App\Models\OrderManagement::getAllQtyByStatus($value->order_management_id, $value->id, \App\Models\Shipment::SHIPMENT_STATUS_READY_TO_SHIP);
                                $getAllQtyByWaitingStock = \App\Models\OrderManagement::getAllQtyByStatus($value->order_management_id, $value->id, \App\Models\Shipment::SHIPMENT_STATUS_PENDING_STOCK);
                                $getAllQtyByCancelled = \App\Models\OrderManagement::getAllQtyByStatus($value->order_management_id, $value->id, \App\Models\Shipment::SHIPMENT_STATUS_CANCEL);
                                $getAllQtyByShipped = \App\Models\OrderManagement::getAllQtyByStatus($value->order_management_id, $value->id, \App\Models\Shipment::SHIPMENT_STATUS_SHIPPED);
                            @endphp
                            <td>
                                @if(isset($getAllQtyByReadyToShip) && $getAllQtyByReadyToShip > 0)
                                    <div>
                                        Ready to Ship : <strong>{{$getAllQtyByReadyToShip}}</strong>
                                    </div>
                                @endif
                                @if(isset($getAllQtyByWaitingStock) && $getAllQtyByWaitingStock > 0)
                                    <div>
                                        Wait for Stock : <strong>{{$getAllQtyByWaitingStock}}</strong>
                                    </div>
                                @endif
                                @if(isset($getAllQtyByCancelled) && $getAllQtyByCancelled > 0)
                                    <div>
                                        Cancelled : <strong>{{$getAllQtyByCancelled}}</strong>
                                    </div>
                                @endif
                                @if(isset($getAllQtyByShipped) && $getAllQtyByShipped > 0)
                                    <div>
                                        Shipped : <strong>{{$getAllQtyByShipped}}</strong>
                                    </div>
                                @endif
                                @if($getAllQtyByReadyToShip <= 0 && $getAllQtyByWaitingStock <= 0 && $getAllQtyByCancelled <= 0 && $getAllQtyByShipped <= 0)
                                    <div>
                                        <strong>-</strong>
                                    </div>
                                @endif
                            </td>
                            <td>
                                {{$value->ordered_qty - ($getAllQtyByReadyToShip + $getAllQtyByWaitingStock + $getAllQtyByCancelled + $getAllQtyByShipped)}}
                            </td>
                        </tr>
                    @endforeach
                @endif
                </tbody>
            </table>
        </div>

        <div id="add_new_shipment_div" class="text-center mb-8">
            <a id="add_new_shipment" class="h-8 relative top-[0.10rem] inline-flex items-center justify-center px-3 py-2 bg-green-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-600 active:bg-green-600 outline-none focus:outline-none focus:border-green-600 focus:ring ring-green-300 disabled:opacity-25 transition ease-in-out duration-150"  color="green">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                    <path d="M11 3a1 1 0 100 2h2.586l-6.293 6.293a1 1 0 101.414 1.414L15 6.414V9a1 1 0 102 0V4a1 1 0 00-1-1h-5z"></path>
                    <path d="M5 5a2 2 0 00-2 2v8a2 2 0 002 2h8a2 2 0 002-2v-3a1 1 0 10-2 0v3H5V7h3a1 1 0 000-2H5z"></path>
                </svg>
                <span class="ml-2">
                    Add New Shipment
                </span>
            </a>
        </div>
    </div>
    <div class="col-lg-12">
    <table class="table-auto border-collapse w-100  border tbl_border mb-6 text-center" id="orders_shipments_details_datatable">
        <thead class="border bg-green-300">
            <tr class="rounded-lg text-sm font-medium text-gray-700 text-left">
                <th class="px-4 py-2 text-left"></th>
                <th width="30%" class="px-4 py-2 text-center">Details</th>
            </tr>
        </thead>
        <tbody>

        </tbody>
    </table>
    </div>

    <x-modal.modal-large id="__modalCreateShipmentForOrder" class="modal-hide">
        <x-modal.header>
            <x-modal.title>
                {{ __('translation.New Shipment') }}
            </x-modal.title>
        </x-modal.header>
        <x-modal.body>
            <x-alert-danger id="__alertDangerCreateShipmentForOrder" class="alert mb-5 hidden">
                <div id="__alertDangerContentCreateShipmentForOrder"></div>
            </x-alert-danger>

            <div id="modal_content_create_shipment_for_order"></div>
        </x-modal.body>
    </x-modal.modal-large>


    <x-modal.modal-large id="__modalEditShipmentForOrder" class="modal-hide">
        <x-modal.header>
            <x-modal.title>
                {{ __('translation.Edit Shipment') }}
            </x-modal.title>
            <x-modal.close-button class="__btnCloseModalShipment"/>
        </x-modal.header>
        <x-modal.body>
            <x-alert-danger id="__alertDangerEditShipmentForOrder" class="alert mb-5 hidden">
                <div id="__alertDangerContentEditShipmentForOrder"></div>
            </x-alert-danger>

            <div id="modal_content_edit_shipment_for_order"></div>
        </x-modal.body>
    </x-modal.modal-large>

    <x-modal.modal-small class="modal-hide" id="__modalCancelShipment">
        <x-modal.header>
            <x-modal.title>
                {{ __('translation.Confirm') }}
            </x-modal.title>
        </x-modal.header>
        <x-modal.body>
            <div class="mb-5">
                <p class="text-center">
                    {{ __('order-management.shipment_delete_msg') }}
                </p>
                <input type="hidden" id="shipment_id_value">
            </div>
            <div class="text-center pb-5">
                <x-button type="button" color="gray" id="__btnCloseModalCancelShipment">
                    {{ __('translation.No, Close') }}
                </x-button>
                <x-button-link color="red" id="__btnCloseModalFinalDeleleShipment">
                    {{ __('translation.Yes, Continue') }}
                </x-button-link>
            </div>
        </x-modal.body>
    </x-modal.modal-small>

    <div class="modal" tabindex="-1" role="dialog" id="print_level_modal">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <strong>{{ __('translation.Create Print Level') }}</strong>
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
                        <div id="order_details"></div>
                    </div>
                    <div class="mt-4 text-center">
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
                        <div id="order_details_pack"></div>

                        <div class="mt-4 text-center">
                            <input class="btn btn-success" style="margin: 0 auto; padding: 5px 10px;" type="button" onclick="confirmPacking()" value="Confirm Packing" />
                            <form method="POST" action="{{route('printLevelPrint')}}" enctype="multipart/form-data">
                                @csrf
                                <input type="hidden" id="shipment_id_input_val_pack" name="shipment_id_input_val_pack">
                                <input type="hidden" id="order_id_input_val_pack" name="order_id_input_val_pack">
                            </form>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
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
                        <option value="10">{{ __('translation.Wanting for Stock') }}</option>
                        <option value="11">{{ __('translation.Ready To ship') }}</option>
                        <option value="12">{{ __('translation.Shipped') }}</option>
                        <option value="13">{{ __('translation.Cancelled') }}</option>
                        <option value="14">{{ __('translation.Ready To ship (Printed)') }}</option>
                      
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

    <!-- included all js for edit Order Shipments -->
    <script src="{{asset('js/dataTables.checkboxes.min.js')}}"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.19/dist/sweetalert2.min.js"></script>
    @include('seller.order_management.edit_order_shipments_script')

