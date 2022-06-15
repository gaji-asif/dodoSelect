	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.19/dist/sweetalert2.min.css">
    <x-card.header>
        <x-card.back-button href="{{ route('order_management.index') }}" id="left_pad" />
        <x-card.title>
            {{ __('translation.Shipment Details') }} #{{$order_id}}
        </x-card.title>
    </x-card.header>
    <div class="custom_ship_content">
    <div class="col-lg-12 tabs mb-3">
      <div id="add_new_custom_shipment_div" class="text-center mb-2">
          <a id="add_new_custom_shipment" class="h-8 relative top-[0.10rem] inline-flex items-center justify-center px-3 py-2 bg-green-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-600 active:bg-green-600 outline-none focus:outline-none focus:border-green-600 focus:ring ring-green-300 disabled:opacity-25 transition ease-in-out duration-150"  color="green">
           <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
            <path d="M11 3a1 1 0 100 2h2.586l-6.293 6.293a1 1 0 101.414 1.414L15 6.414V9a1 1 0 102 0V4a1 1 0 00-1-1h-5z"></path>
            <path d="M5 5a2 2 0 00-2 2v8a2 2 0 002 2h8a2 2 0 002-2v-3a1 1 0 10-2 0v3H5V7h3a1 1 0 000-2H5z"></path>
          </svg>
          <span class="ml-2">
            {{__('translation. Add New Shipment')}}
          </span>
        </a>
      </div>
    </div>
    <x-section.title>
    {{__('translation. Custom Shipments')}}
    </x-section.title>
    
    <div class="row custom_padding_left_right_15">
        @if(isset($allShipments) && count($allShipments)>0)
            <!-- <h6 class="mt-4"><strong>All Custom Shipments : </strong></h6> -->
            @foreach($allShipments as $rows)
                <div class="w-full overflow-x-auto">
                 <table class="table-auto border-collapse w-100  border tbl_border mb-6 text-center" id="orders_custom_shipments_details_datatable">
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
            @endforeach
            @else
           <div class="text-center col-lg-12"> -- No Shipment created -- </div>
            @endif
    </div>

    <x-modal.modal-large id="__modalCreateCustomShipmentForOrder" class="modal-hide">
        <x-modal.header>
            <x-modal.title>
                {{__('translation. New Custom Shipment')}}
            </x-modal.title>
        </x-modal.header>
        <x-modal.body>
            <x-alert-danger id="__alertDangerCreateCustomShipmentForOrder" class="alert mb-5 hidden">
                <div id="__alertDangerContentCreateCustomShipmentForOrder"></div>
            </x-alert-danger>

           <div id="modal_content_create_custom_shipment_for_order"></div>
        </x-modal.body>
    </x-modal.modal-large>

    <x-modal.modal-large id="__modalEditCustomShipmentForOrder" class="modal-hide">
        <x-modal.header>
            <x-modal.title>
                {{__('translation. Edit Custom Shipment')}}
            </x-modal.title>
        </x-modal.header>
        <x-modal.body>
            <x-alert-danger id="__alertDangerEditCustomShipmentForOrder" class="alert mb-5 hidden">
                <div id="__alertDangerContentEditCustomShipmentForOrder"></div>
            </x-alert-danger>

           <div id="modal_content_edit_custom_shipment_for_order"></div>
        </x-modal.body>
    </x-modal.modal-large>

    <x-modal.modal-large id="__modalEditCustomShipmentForOrder" class="modal-hide">
        <x-modal.header>
            <x-modal.title>
                Edit Shipment
            </x-modal.title>
        </x-modal.header>
        <x-modal.body>
            <x-alert-danger id="__alertDangerEditCustomShipmentForOrder" class="alert mb-5 hidden">
                <div id="__alertDangerContentEditCustomShipmentForOrder"></div>
            </x-alert-danger>

           <div id="modal_content_edit_custom_shipment_for_order"></div>
        </x-modal.body>
    </x-modal.modal-large>

    <x-modal.modal-small class="modal-hide" id="__modalCancelCustomShipment">
            <x-modal.header>
                <x-modal.title>
                    {{ __('translation.Confirm') }}
                </x-modal.title>
            </x-modal.header>
            <x-modal.body>
                <div class="mb-5">
                    <p class="text-center">
                        {{ __('translation.Are your Sure Your want to delete the shipment?') }}
                    </p>
                    <input type="hidden" id="shipment_id_value_custom">
                </div>
                <div class="text-center pb-5">
                    <x-button type="button" color="gray" id="__btnCloseModalCancelCustomShipment">
                        {{ __('translation.No, Close') }}
                    </x-button>
                    <x-button-link color="red" id="__btnCloseModalFinalDeleleCustomShipment">
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
    </div>

    <!-- included all js for edit Order Shipments -->
    <script src="{{asset('js/dataTables.checkboxes.min.js')}}"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.19/dist/sweetalert2.min.js"></script>
    @include('seller.order_management.edit_order_custom_shipments_script')