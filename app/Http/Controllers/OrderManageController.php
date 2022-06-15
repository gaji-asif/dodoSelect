<?php

namespace App\Http\Controllers;

use App\Actions\OrderManagement\CalculateTotalAmountUpdateAction;
use App\Custom\Ksherpay\KsherPay;
use App\Http\Requests\OrderManagement\DatatableRequest;
use App\Http\Requests\OrderManagement\StoreRequest;
use App\Http\Requests\OrderManagement\UpdateRequest;
use App\Http\Resources\ProductTypeAheadResource;
use App\Models\Channel;
use App\Models\Customer;
use App\Models\OrderManagement;
use App\Models\OrderManagementDetail;
use App\Models\Permission;
use App\Models\Product;
use App\Models\Payment;
use App\Models\PaymentManual;
use App\Models\ProductPrice;
use App\Models\Category;
use App\Models\CustomerShippingMethod;
use App\Models\Shipment;
use App\Models\ShipmentProduct;
use App\Models\ShippingCost;
use App\Models\TaxRateSetting;
use App\Models\User;
use App\Models\WooOrderPurchase;
use Illuminate\Http\Request;
use Datatables;
use Jenssegers\Agent\Agent;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;
use Carbon\Carbon;
use DateTime;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Response;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Str;
use App\Models\ShopeeOrderPurchase;
use Illuminate\Support\Facades\Storage;

/**
 * @TODO Lot of strings without translation method, refactor them like __('translation.Your String')
 */
class OrderManageController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\View\View
     */
    public function index($customerType = '0')
    {
        $sellerId = Auth::user()->id;
        $roleName = Auth::user()->role;

        $totalProcessingOrders = OrderManagement::where('seller_id', $sellerId)
                                ->where('order_status', OrderManagement::ORDER_STATUS_PROCESSING)
                                ->customerAsset($roleName)
                                ->count();

        $totalProcessingDropshipperOrders = OrderManagement::where('seller_id', $sellerId)
                                ->where('order_status', OrderManagement::ORDER_STATUS_PROCESSING)
                                ->customerAsset($roleName, User::ROLE_DROPSHIPPER)
                                ->count();

        $totalProcessingWooCommerce = WooOrderPurchase::where('seller_id', $sellerId)
                                        ->where('status', WooOrderPurchase::ORDER_STATUS_PROCESSING)
                                        ->count();

        $statusSchema = OrderManagement::getStatusSchemaForDatatable($roleName, $customerType);
        $defaultStatusOrderId = array_column($statusSchema[0]['sub_status'], 'id')[0];

        $data = [
            'totalProcessingOrders' => $totalProcessingOrders,
            'totalProcessingDropshipperOrders' => $totalProcessingDropshipperOrders,
            'totalProcessingWooCommerce' => $totalProcessingWooCommerce,
            'statusSchema' => $statusSchema,
            'defaultStatusOrderId' => $defaultStatusOrderId,
            'customerType' => $customerType
        ];

        return view('seller.order_management.index', $data);
    }

    /**
     * Handle server-side datatable of order managements
     *
     * @param  \App\Http\Requests\OrderManagement\DatatableRequest  $request
     * @return Response
     */
    public function data(DatatableRequest $request)
    {
        $sellerId = Auth::user()->id;

        $orderManagementsTable = (new OrderManagement())->getTable();
        $customersTable = (new Customer())->getTable();
        $shipmentTable = (new Shipment())->getTable();

        $orderStatusId = $request->get('status', 0);

        $search = isset($request->get('search')['value'])
                ? $request->get('search')['value']
                : null;

        $orderColumnIndex = isset($request->get('order')[0]['column'])
                            ? $request->get('order')[0]['column']
                            : 1;
        $orderDir = isset($request->get('order')[0]['dir'])
                    ? $request->get('order')[0]['dir']
                    : 'desc';

        $availableColumnsOrder = [
            'id', 'id'
        ];

        $orderColumnName = isset($availableColumnsOrder[$orderColumnIndex])
                            ? $availableColumnsOrder[$orderColumnIndex]
                            : $availableColumnsOrder[0];

        $roleName = Auth::user()->role;

        $customerType = '0';
        if(!empty($request->customerType)){
            $customerType = $request->customerType;
        }

        $orderManagements = OrderManagement::selectRaw("{$orderManagementsTable}.*,
                                {$customersTable}.customer_name AS customer_customer_name,
                                {$customersTable}.contact_phone AS customer_contact_phone,
                                {$shipmentTable}.id AS shipment_row_id")
                ->where("{$orderManagementsTable}.seller_id", $sellerId)
                ->with('channels')
                ->with('shipment')
                ->with('shop')
                ->withCount('order_management_details')
                ->joinedDatatable()
                ->customerAsset($roleName, $customerType)
                ->byOrderShipmentStatus($orderStatusId)
                ->searchDatatable($search)
                ->orderBy($orderColumnName, $orderDir)
                ->groupByOrderStatus($orderStatusId)
                ->get();

        return Datatables::of($orderManagements)
                ->addIndexColumn()
                ->addColumn('checkbox', function ($row) {
                    return $row->id;
                })
                ->addColumn('order_data', function ($row) use ($orderStatusId) {
                    $currentShipment = Shipment::where('id', $row->shipment_row_id)->first();
                    $paymentStatus = '
                        <span class="badge-status--red">
                            '. $row->str_payment_status .'
                        </span>
                    ';

                    if ($row->payment_status == OrderManagement::PAYMENT_STATUS_PAID) {
                        $paymentMethod = '';
                        if ($row->payment_method == OrderManagement::PAYMENT_METHOD_BANK_TRANSFER)
                            $paymentMethod = ' by Bank Transfer';
                        elseif ($row->payment_method == OrderManagement::PAYMENT_METHOD_INSTANT)
                            $paymentMethod = ' by Instant Pay';

                        $paymentStatus = '
                            <span class="badge-status--green">
                                '. $row->str_payment_status . $paymentMethod .'
                            </span>
                        ';
                    }

                    $channelName = $row->channels->name;
                    $shopName = $row->shop->name;

                    if (Storage::disk('s3')->exists($row->channels->image) && !empty($row->channels->image)) {
                        $image = Storage::disk('s3')->url($row->channels->image);
                    }
                    else {
                        $image = Storage::disk('s3')->url('uploads/No-Image-Found.png');
                    }

                    if ($row->customer_type == 1) {
                        $channelName = 'Dropshipper';
                    }

                    $shippingMethod = '';
                    foreach ($row->customer_shipping_methods as $customerShipping){
                        if ($customerShipping->is_selected == CustomerShippingMethod::IS_SELECTED_YES){
                            $shippingMethod = $customerShipping->shipping_cost->name;
                        }
                    }

                    if (Auth::user()->role == 'dropshipper'){
                        $cancelBtn = '';
                        if ($row->order_status == OrderManagement::ORDER_STATUS_PENDING || $row->order_status == OrderManagement::ORDER_STATUS_PENDING_PAYMENT) {
                            $publicPageBtn = '<a href="'. route('order-management.public-url', [ 'order_id' => $row->order_id ]) .'" target="_blank" class="btn-action--blue" title="Public Page">
                                                <i class="fas fa-share-alt"></i>
                                                <span class="ml-2 hidden lg:inline">Pay Link</span>
                                            </a>';

                            $cancelBtn = '<button type="button" class="ml-2 btn-action--red BtnCancel" title="Cancel"
                                                data-id="'. $row->id .'"
                                                data-order_id="'. $row->id .'">
                                                <i class="fas fa-trash"></i>
                                                <span class="ml-2 hidden lg:inline">Cancel Order</span>
                                            </button>';
                        } else {
                            $publicPageBtn = '<a href="'. route('order-management.public-url', [ 'order_id' => $row->order_id ]) .'" target="_blank" class="btn-action--blue" title="Public Page">
                                                <i class="fas fa-share-alt"></i>
                                                <span class="ml-2 hidden lg:inline">Public Page</span>
                                            </a>';
                        }

                        $functionalBtns = $publicPageBtn . $cancelBtn;
                    }
                    else{
                        $confirmBtn = '';
                        $cancelBtn = '';
                        $printLabelBtn = '';
                        $publicPageBtn = '';
                        $shipmentStatusBtns = '';
                        $updateStatusBtnForProcessedAction = '';
                        $updateStatusBtnForProcessed = '<button type="button" class="btn-action--yellow mr-1" title="Update Status"
                                                data-order-id="' . $row->id . '"
                                                data-shipment-id="' . $row->shipment_row_id . '"
                                                onClick="updateStatusForProcessed(this)">
                                                <i class="fa fa-edit mr-2" aria-hidden="true"></i>
                                                Update status
                                            </button>
                                            ';


                        $array_order_status_id = array('2','8','9');

                        if(in_array($orderStatusId, $array_order_status_id) AND $row->order_status == OrderManagement::ORDER_STATUS_PROCESSED){
                            $updateStatusBtnForProcessedAction = $updateStatusBtnForProcessed;
                        }
                        else{
                            $updateStatusBtnForProcessedAction = '';
                        }

                        if ($orderStatusId >= 10 || $row->order_status == OrderManagement::ORDER_STATUS_PROCESSED) {
                            $publicPageBtn = '';
                        }
                        elseif ($row->order_status == OrderManagement::ORDER_STATUS_CANCEL) {
                            $publicPageBtn = '<a href="'. route('order-management.public-url', [ 'order_id' => $row->order_id ]) .'" target="_blank" class="btn-action--blue" title="Public Page">
                                                <i class="fas fa-share-alt"></i>
                                                <span class="ml-2 hidden lg:inline">Public Page</span>
                                            </a>';
                        }
                        elseif ($row->order_status == OrderManagement::ORDER_STATUS_PROCESSING) {
                            $publicPageBtn = '  <button type="button" class="btn-action--green"
                                                    data-id="' . $row->id . '"
                                                    onClick="createShipment(this)">
                                                    <i class="fa fa-shipping-fast mr-1" aria-hidden="true"></i>
                                                    '.__('translation.Arrange Shipment').'
                                                </button>';
                        }
                        else {
                            if($row->order_status != OrderManagement::ORDER_STATUS_COMPLETED){
                            $publicPageBtn = '<a href="'. route('order-management.public-url', [ 'order_id' => $row->order_id ]) .'" target="_blank" class="btn-action--blue btn_process" title="Pay Link">
                                                <i class="fas fa-share-alt"></i>
                                                <span class="ml-2 mr-2 hidden lg:inline">Pay Link</span>
                                            </a>';
                            }
                        }
                        if ($row->order_status == OrderManagement::ORDER_STATUS_PENDING || $row->order_status == OrderManagement::ORDER_STATUS_PENDING_PAYMENT) {
                            $cancelBtn = '<button type="button" class="mr-2 btn-action--red BtnCancel" title="Cancel"
                                                data-id="' . $row->id . '"
                                                data-order_id="' . $row->id . '">
                                                <i class="fas fa-trash"></i>
                                                <span class="ml-2 hidden lg:inline">Cancel Order</span>
                                            </button>';
                        }
                        if ($row->order_status == OrderManagement::ORDER_STATUS_PAYMENT_UNCONFIRMED) {
                            $confirmBtn = '<button type="button" class="ml-2 btn-action--red confirmPayment" title="Confirm Payment" id="confirmPayment"
                                                data-id="' . $row->id . '"
                                                data-order_id="' . $row->id . '">
                                                <i class="fas fa-check"></i>
                                                <span class="ml-2 hidden lg:inline">Confirm Payment</span>
                                            </button>';
                        }
                        $breakLine = '<span class="sm:hidden"><br></span>';

                        if ($orderStatusId == Shipment::SHIPMENT_STATUS_READY_TO_SHIP || $orderStatusId == Shipment::SHIPMENT_STATUS_READY_TO_SHIP_PRINTED){
                            if ($currentShipment->pack_status == 1){
                                $packageContent = '<div class="mb-1 sm:mb-2">
                                                         Pick Confirm At : ' . date('d-m-Y h:i A', strtotime($currentShipment->packed_date_time)) . ' ( ' . $currentShipment->packer->name . ' )
                                                    </div>
                                                    ' . $breakLine;
                            }
                            else{
                                $packageContent = '<button type="button" class="btn-action--blue mr-2" title="Pack Order"
                                                        data-order-id="' . $row->id . '"
                                                        data-shipment-id="' . $row->shipment_row_id . '"
                                                        onClick="packOrder(this)">
                                                        <i class="fa fa-truck-pickup mr-2" aria-hidden="true"></i>
                                                        PICK CONFIRM
                                                    </button>
                                                    ' . $breakLine;
                            }
                            $updateStatusBtn = '<button type="button" class="btn-action--yellow mr-1" title="Update Status"
                                                    data-order-id="' . $row->id . '"
                                                    data-shipment-id="' . $row->shipment_row_id . '"
                                                    onClick="updateStatus(this)">
                                                    Update status
                                                </button>
                                                ';

                            if ($orderStatusId >= 10 && $orderStatusId != Shipment::SHIPMENT_STATUS_CANCEL) {
                                $printLabelBtn = '
                                              <a style="position:absolute;margin-left:10px;" href="' . route('shipment-label.pdf', ['order_id' => $row->id, 'shipment_id' => $row->shipment_row_id]) . '">
                                                  <button type="button" class="btn-action--green mr-2" title="Print Label">
                                                      <i class="fa fa-print mr-2" aria-hidden="true"></i>
                                                      PRINT LABEL
                                                  </button>
                                              </a>
                                               ';
                            }

                            $shipmentStatusBtns = $packageContent . $updateStatusBtn . $printLabelBtn;
                        }
                        $functionalBtns = $publicPageBtn . $cancelBtn . $confirmBtn . $shipmentStatusBtns;
                    }

                    $rowId = $row->id;
                    if ($orderStatusId >= 10){
                        $rowId = $row->id . ' (Ship ID #' . $row->shipment_row_id . ')';
                    }

                    $printDate = '';
                    if ($orderStatusId >= 10 && $currentShipment->print_status == 1){
                        $printDate = '<div class="mb-1 sm:mb-2">
                                             Printed At : ' . date('d-m-Y h:i A', strtotime($currentShipment->print_date_time)) . ' ( ' . $currentShipment->printer->name . ' )
                                        </div>
                                        ';
                    }

                    $totalItemsTitle = 'Total Amount : ';
                    if ($orderStatusId == Shipment::SHIPMENT_STATUS_READY_TO_SHIP){
                        $totalItemsTitle = 'Total Items : ';
                    }

                    $statusStr = OrderManagement::getOrderStatus($orderStatusId);

                    if(!empty($currentShipment->shipment_date)){
                        $shipment_date = 'Shipment Date: <strong>'.date('d/m/Y',strtotime($currentShipment->shipment_date)).'</strong>';
                    }
                    else{
                        $shipment_date='';
                    }

                    $vatRequest = OrderManagement::isVatRequest($row->id);
                    if($vatRequest == 1){
                        $isVatReq = '<a href="'.route('tax-invoice.pdf-invoice', [ 'order_id' => $row->id ]).'"><button type="button" class="btn btn-warning btn-print-pdf">'.__('translation.VAT Requested').'</button></a>';

                    }
                    else{
                        $isVatReq = '';
                    }

                    return '
                        <div class="border border-solid border-gray-400 lg:border-gray-300 rounded-md hover:bg-blue-50">
                            <div class="border border-dashed border-t-0 border-r-0 border-l-0 border-gray-300">
                                <div class="grid grid-cols-3">
                                    <div class="col-span-3 sm:col-span-1">
                                        <a href="'. route('order_management.edit', [ 'order_management' => $row ]) .'" data-id="'.$row->id.'" order-status-id="'.$row->order_status.'" class="cursor-pointer underline" title="Edit">
                                            <div class="text-center px-2 py-1 sm:py-2">
                                                <span class="font-bold text-gray-400">#</span>
                                                <span class="relative -left-1 text-blue-500 font-bold">
                                                    '. $rowId .'
                                                </span>
                                            </div>
                                        </a>
                                    </div>
                                    <div class="col-span-3 sm:col-span-1">
                                        <div class="px-2 py-1 sm:py-2">
                                            <span class="text-xs sm:text-sm">
                                                '. strtoupper($statusStr) .'
                                            </span>
                                        </div>
                                    </div>
                                    <div class="col-span-3 sm:col-span-1">
                                        <div class="px-2 py-1 sm:py-2">
                                            '. $shipment_date .'

                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div>
                                <div class="grid grid-cols-1 lg:grid-cols-3">
                                    <div class="lg:col-span-2">
                                        <div class="grid grid-cols-5">
                                            <div class="col-span-5 sm:col-span-2 px-2 py-2">
                                                <div class="mb-1 md:mb-3">
                                                    <img src="'. $image .'" alt="'. $channelName .'" title="'. $channelName .'" class="w-10 h-auto" />
                                                </div>
                                                <div class="">
                                                    <span class="badge-status--yellow">
                                                        '. $shopName .'
                                                    </span>
                                                </div>
                                                <div>
                                                    <span class="">
                                                        '. $row->contact_name .'
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="col-span-5 sm:col-span-3">
                                                <div class="text-left px-2 py-2">
                                                    <div>
                                                        <span class="text-gray-600">
                                                            Cust. Name :
                                                        </span>'. $row->customer_customer_name .'
                                                    </div>
                                                    <div>
                                                        <span class="text-gray-600">
                                                            '. $totalItemsTitle .'
                                                        </span> '. currency_symbol('THB') . number_format((float)$row->in_total, 2, '.', '').'
                                                       ( <a data-order-id="' . $row->id . '" data-shipment-id="' . $row->shipment_row_id . '" class="modal-open cursor-pointer" onClick="productsOrdered(this)">' . $row->order_management_details_count .' Item/s</a> )
                                                    </div>
                                                    <div class="mb-2">
                                                        <span class="text-gray-600">
                                                            Shipped Method:
                                                        </span>
                                                        <a data-id="'. $row->id .'" id="BtnAddress" class="modal-open cursor-pointer">' . $shippingMethod .'</a>
                                                    </div>
                                                    <div class="mb-3 text_uppercase">
                                                        <a id="" class="modal-open cursor-pointer text_uppercase"><font class="">' . $paymentStatus .'</font></a>
                                                    </div>

                                                    <div class="text-center sm:text-left">
                                                         '. $printDate .'
                                                    </div>
                                                    <div class="text-center sm:text-left">
                                                         '. $functionalBtns .'
                                                         '.$updateStatusBtnForProcessedAction.'
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="lg:col-span-1">
                                        <div class="border border-dashed border-r-0 border-b-0 border-l-0 border-gray-300">
                                            <div class="px-2 py-2 lg:text-left">
                                                <div class="text-xs sm:text-sm">
                                                    <span class="text-gray-600">Order Date :</span> '. date('d/m/Y h:i a', strtotime($row->created_at)) .'
                                                </div>
                                                <div class="text-xs sm:text-sm">
                                                    <span class="text-gray-600">Created By :</span> '. $row->creator->name .'
                                                </div>
                                                 <div class="text-xs sm:text-sm mt-4 font_bold">
                                                    '. $isVatReq .'
                                                </div>

                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    ';
                })
                ->rawColumns(['checkbox', 'order_data'])
                ->make(true);
    }

    /**
     * Show the form for creating a new resource.
     * @return Response
     */
    public function createOrderId($len=16)
    {
        $nonce_str = Str::random($len);
        return $nonce_str;
    }

    /**
     * Show create order_managemenst page
     *
     * @return \Illuminate\View\View
     */
    public function create($customerType = '0')
    {
        $sellerId = Auth::user()->id;

        Session::put('itemArray', []);

        $order_id = '';
        do {
            $order_id = $this->createOrderId();
            $orderManagement = OrderManagement::where('seller_id', $sellerId)
                                    ->where('order_id', $order_id)
                                    ->first();
        } while(!empty($orderManagement));

        if ($customerType == '0')
            $channels = Channel::where('seller_id', $sellerId)->where('display_channel', 1)->orderBy('name')->get();
        else
            $channels = Channel::where('name', 'Dropshipper')->first();



        $products = Product::where('seller_id', Auth::user()->id)->get();

        $data = [
            'order_id' => $order_id,
            'channels' => $channels,
            'products' => ProductTypeAheadResource::collection($products),
            'taxEnableValues' => OrderManagement::getAllTaxEnableValues(),
            'taxEnableYes' => OrderManagement::TAX_ENABLE_YES,
            'taxEnableNo' => OrderManagement::TAX_ENABLE_NO,
            'taxRateSetting' => TaxRateSetting::where('seller_id', $sellerId)->first(),
            'customerType' => $customerType
        ];

        return view('seller.order_management.create', $data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreRequest $request
     * @param  \App\Actions\OrderManagement\CalculateTotalAmountUpdateAction $calculateTotalAmount
     * @return Response
     */
    public function store(StoreRequest $request, CalculateTotalAmountUpdateAction $calculateTotalAmount)
    {
        try {
            $sellerId = Auth::user()->id;
            $createdBy = Auth::user()->id;

            $orderId = $request->order_id;

            $taxRateSettingTable = (new TaxRateSetting())->getTable();

            DB::beginTransaction();

            $paymentUrl = '';
            if ($request->payment_method == 2) {
                $paymentUrl = $this->createPayment($request->in_total,$request->order_id);
            }

             $customerData = [
                'customer_name' => $request->customer_name,
                'contact_phone' => $request->contact_phone,
                'seller_id' => $sellerId,
            ];

            $customer = Customer::where($customerData)->first();

            if (!empty($customer->id)){
                $customerId = $customer->id;
            } else {
                $customerData = new Customer();
                $customerData->customer_name = $request->customer_name;
                $customerData->contact_phone = $request->contact_phone;
                $customerData->seller_id = $sellerId;
                $customerData->order_type = Customer::ORDER_TYPE_DEFAULT;
                $customerData->created_at = new DateTime();
                $customerData->save();
                $customerId = $customerData->id;
            }

            if ($request->customer_type == '0'){
                $shopId = $request->shop_id;
                $contactName = $request->contact_name;
                $customerType = 0;
                $userId = null;
            } else {
                $dropshipper = User::where('role', 'dropshipper')->where('customer_id', $customerId)->first();
                $shopId = $dropshipper->shop_id;
                $contactName = $dropshipper->phone;
                $customerType = 1;
                $userId = $dropshipper->id;
            }

            $taxRate = 0;
            if ($request->tax_enable == OrderManagement::TAX_ENABLE_YES) {
                $taxRateSetting = DB::table($taxRateSettingTable)->where('seller_id', $sellerId)->first();
                if(isset($taxRateSetting->tax_rate)){
                    $taxRate = $taxRateSetting->tax_rate;
                }
                else{
                    $taxRate = 0;
                }
            }
            $tax_vat_amount = $request->tax_vat_amount;

            $orderManagementData = new OrderManagement();
            $orderManagementData->shop_id = $shopId;
            $orderManagementData->channel_id = $request->channel_id;
            $orderManagementData->customer_id = $customerId;
            $orderManagementData->contact_name = $contactName;
            $orderManagementData->payment_url = $paymentUrl;
            $orderManagementData->payment_method = $request->payment_method;
            $orderManagementData->order_id = $orderId;
            $orderManagementData->order_status = OrderManagement::ORDER_STATUS_PENDING;
            $orderManagementData->customer_type = $customerType;
            $orderManagementData->seller_id= $sellerId;
            $orderManagementData->encrepted_order_id= Crypt::encryptString($orderId);
            //$orderManagementData->tax_rate= $taxRate;
            $orderManagementData->tax_rate= $request->tax_vat_amount;
            $orderManagementData->tax_enable= $request->tax_enable;
            $orderManagementData->tax_number= $request->tax_number;
            $orderManagementData->company_name= $request->company_name;
            $orderManagementData->company_phone_number= $request->company_phone_number;
            $orderManagementData->company_contact_name= $request->company_contact_name;
            $orderManagementData->company_address= $request->company_address;
            $orderManagementData->company_province= $request->company_province;
            $orderManagementData->company_district= $request->company_district;
            $orderManagementData->company_sub_district= $request->company_sub_district;
            $orderManagementData->company_postcode= $request->company_postcode;
            $orderManagementData->created_by= $createdBy;
            $orderManagementData->user_id= $userId;
            $orderManagementData->created_at = new DateTime();
            $orderManagementData->shipping_name= $request->shipping_name;
            $orderManagementData->shipping_phone= $request->shipping_phone;
            $orderManagementData->shipping_address= $request->shipping_address;
            $orderManagementData->shipping_district= $request->shipping_district;
            $orderManagementData->shipping_sub_district= $request->shipping_sub_district;
            $orderManagementData->shipping_province= $request->shipping_province;
            $orderManagementData->shipping_postcode= $request->shipping_postcode;
            $orderManagementData->save();
            $orderManagementId = $orderManagementData->id;

            $subTotal = 0;
            $shippingCostTotal = 0;
            $discountTotal = 0;
            $inTotalDiscount = 0;
            $totalAmount = 0;

            $totalProductWeight = 0;

            foreach ($request->product_id as $idx => $productId) {
                $productOriginPrice = $request->product_price[$idx] ?? 0;
                $productWeight = $request->product_weight[$idx] ?? 0;
                $orderQty = $request->product_qty[$idx] ?? 0;

                if ($request->customer_type == '0') {
                    $productDiscount = $request->product_discount[$idx] ?? 0;
                }
                else {
                    $productDropshipPrice = $request->dropship_price[$idx] ?? 0;
                    if ($productDropshipPrice == $productOriginPrice)
                        $productDiscount = 0;
                    else
                        $productDiscount = $productOriginPrice - $productDropshipPrice;
                }

                $subTotal += $productOriginPrice * $orderQty;
                $discountTotal += $productDiscount * $orderQty;

                if($productDiscount == 0){
                    $inTotalDiscount += 0;
                }
                else{
                    $inTotalDiscount += ($productOriginPrice - $productDiscount)*$orderQty;
                }

                $totalProductWeight += $productWeight * $orderQty;


                $orderManagementDetailData = new orderManagementDetail();
                $orderManagementDetailData->order_management_id = $orderManagementId;
                $orderManagementDetailData->product_id = $productId;
                $orderManagementDetailData->shop_id = $shopId;
                $orderManagementDetailData->quantity = $orderQty;
                $orderManagementDetailData->price = $productOriginPrice;
                $orderManagementDetailData->discount_price = $productDiscount;
                $orderManagementDetailData->seller_id = $sellerId;
                $orderManagementDetailData->created_at = new DateTime();
                $orderManagementDetailData->save();

            }


            foreach ($request->shipping_method_id as $idx => $shippingMethodId) {
                $shippingCostId = $shippingMethodId;
                $shippingMethodName = $request->shipping_method_name[$idx] ?? '';
                $shippingMethodPrice = $request->shipping_method_price[$idx] ?? 0;
                $shippingMethodDiscount = $request->shipping_method_discount[$idx] ?? 0;
                $shippingMethodSelected = $request->shipping_method_selected[$idx] ?? 0;

                if ($shippingMethodSelected == CustomerShippingMethod::IS_SELECTED_YES) {
                    $shippingCostTotal += $shippingMethodPrice-$shippingMethodDiscount;
                    $discountTotal += $shippingMethodDiscount;
                }

                $isNewStatus = CustomerShippingMethod::IS_NEW_STATUS_NO;
                if (empty($shippingMethodId)) {
                    $isNewStatus = CustomerShippingMethod::IS_NEW_STATUS_YES;

                    $shippingCostData = new ShippingCost();
                    $shippingCostData->shipper_id = 0;
                    $shippingCostData->name = $shippingMethodName;
                    $shippingCostData->weight_from = 0;
                    $shippingCostData->weight_to = 0;
                    $shippingCostData->price = $shippingMethodPrice;
                    $shippingCostData->created_at = new DateTime();
                    $shippingCostData->save();

                }

                $customerShippingMethodData = new CustomerShippingMethod();
                $customerShippingMethodData->order_id = $orderManagementId;
                $customerShippingMethodData->shipping_cost_id = $shippingCostId;
                $customerShippingMethodData->price = $shippingMethodPrice;
                $customerShippingMethodData->discount_price = $shippingMethodDiscount;
                $customerShippingMethodData->is_selected = $shippingMethodSelected;
                $customerShippingMethodData->enable_status = CustomerShippingMethod::ENABLE_STATUS_YES;
                $customerShippingMethodData->is_new_status = $isNewStatus;
                $customerShippingMethodData->save();

            }

            $totalAmount = $calculateTotalAmount->handle($subTotal, $tax_vat_amount, $shippingCostTotal, $inTotalDiscount);
            $orderManagement = OrderManagement::where('id', $orderManagementId)->first();

            $orderManagementsData = OrderManagement::find($orderManagementId);
            $orderManagementsData->sub_total = $subTotal;
            $orderManagementsData->shipping_cost = $shippingCostTotal;
            $orderManagementsData->in_total = $totalAmount;
            $orderManagementsData->total_product_weight = round($totalProductWeight);
            $orderManagementsData->update();

            DB::commit();

            $in_total = ($subTotal+$shippingCostTotal)-$inTotalDiscount;
            // $vat = ($subTotal-$inTotalDiscount);
            // $vatFinal = ($vat*$taxRate)/100;

            $responseData = [
                'orderId' => $orderManagementId,
                'subTotal' => $subTotal,
                'shippingCost' => $shippingCostTotal,
                'discountTotal' => $inTotalDiscount,
                'totalAmount' => $in_total,
                'publicUrl' => route('order-management.public-url', [ 'order_id' => $orderId ]),
                'editUrl' => route('order_management.edit', [ 'order_management' => $orderManagement ])
            ];

            return $this->apiResponse(Response::HTTP_CREATED, 'Order created.', $responseData);

        } catch (\Exception $th) {
            report($th);

            DB::rollBack();

            return $this->apiResponse(Response::HTTP_INTERNAL_SERVER_ERROR, 'Sorry, something went wrong. ' . $th->getMessage());
        }
    }

    /**
     * Show create order_managements page for dropshippers
     *
     * @return \Illuminate\View\View
     */
    public function createDropshipperOrder()
    {
        $sellerId = Auth::user()->id;

        Session::put('itemArray', []);

        $order_id = '';
        do {
            $order_id = $this->createOrderId();
            $orderManagement = OrderManagement::where('seller_id', $sellerId)
                ->where('order_id', $order_id)
                ->first();
        } while (!empty($orderManagement));

        $userPermissions = Permission::dropshipperProductPermissions(Auth::user()->dropshipper_id);
        $products = Product::where('seller_id',Auth::user()->id)
            ->whereIn('product_code', $userPermissions)
            ->get();

        $data = [
            'order_id' => $order_id,
            'paymentMethodBankTransfer' => OrderManagement::PAYMENT_METHOD_BANK_TRANSFER,
            'paymentMethodInstant' => OrderManagement::PAYMENT_METHOD_INSTANT,
            'products' => ProductTypeAheadResource::collection($products),
            'taxEnableValues' => OrderManagement::getAllTaxEnableValues(),
            'taxEnableYes' => OrderManagement::TAX_ENABLE_YES,
            'taxEnableNo' => OrderManagement::TAX_ENABLE_NO,
            'taxRateSetting' => TaxRateSetting::where('seller_id', $sellerId)->first()
        ];

        return view('seller.order_management.dropshipper-buyer-page', $data);
    }

    /**
     * Store a newly created resource of Dropshipper in storage.
     *
     * @param StoreRequest $request
     * @return Response
     */
    public function storeDropshipperOrder(StoreRequest $request)
    {
        try {
            $sellerId = Auth::user()->id;
            $createdBy = Auth::user()->dropshipper_id;

            $orderId = $request->order_id;

            $taxRateSettingTable = (new TaxRateSetting())->getTable();

            DB::beginTransaction();

            $shop_id = Auth::user()->shop_id;
            $channel = Channel::where('name', 'Dropshipper')->first();

            if(!empty($channel->id)){
                $channel_id = $channel->id;
            }
            else{
                $channel_id = 0;
            }

            $taxRate = 0;
            if ($request->tax_enable == OrderManagement::TAX_ENABLE_YES) {
                $taxRateSetting = DB::table($taxRateSettingTable)->where('seller_id', $sellerId)->first();
                if(isset($taxRateSetting->tax_rate)){
                    $taxRate = $taxRateSetting->tax_rate;
                }
                else{
                    $taxRate = 0;
                }
            }

            $orderManagementData = new OrderManagement();
            $orderManagementData->shop_id = $shop_id;
            $orderManagementData->channel_id = $channel_id;
            $orderManagementData->customer_id = Auth::user()->customer_id;
            $orderManagementData->contact_name = Auth::user()->phone;
            $orderManagementData->order_id = $orderId;
            $orderManagementData->order_status = OrderManagement::ORDER_STATUS_PENDING;
            $orderManagementData->customer_type = 1;
            $orderManagementData->shipping_address = $request->shipping_address;
            $orderManagementData->shipping_name = $request->shipping_name;
            $orderManagementData->shipping_phone = $request->shipping_phone;
            $orderManagementData->shipping_district = $request->shipping_district;
            $orderManagementData->shipping_sub_district = $request->shipping_sub_district;
            $orderManagementData->shipping_province = $request->shipping_province;
            $orderManagementData->shipping_postcode = $request->shipping_postcode;
            $orderManagementData->seller_id = $sellerId;
            $orderManagementData->encrepted_order_id = Crypt::encryptString($orderId);
            $orderManagementData->tax_rate= $taxRate;
            $orderManagementData->tax_enable= $request->tax_enable;
            $orderManagementData->tax_number= $request->tax_number;
            $orderManagementData->company_name= $request->company_name;
            $orderManagementData->company_phone_number= $request->company_phone_number;
            $orderManagementData->company_contact_name= $request->company_contact_name;
            $orderManagementData->company_address= $request->company_address;
            $orderManagementData->company_province= $request->company_province;
            $orderManagementData->company_district= $request->company_district;
            $orderManagementData->company_sub_district= $request->company_sub_district;
            $orderManagementData->company_postcode= $request->company_postcode;
            $orderManagementData->created_by = $createdBy;
            $orderManagementData->user_id = $createdBy;
            $orderManagementData->created_at = new DateTime();
            $orderManagementData->save();
            $orderManagementId = $orderManagementData->id;

            $subTotal = 0;
            $shippingCostTotal = 0;
            $discountTotal = 0;
            $totalAmount = 0;

            $totalProductWeight = 0;

            foreach ($request->product_id as $idx => $productId) {
                $productOriginPrice = $request->product_price[$idx] ?? 0;
                $productWeight = $request->product_weight[$idx] ?? 0;
                $orderQty = $request->product_qty[$idx] ?? 0;
                $productDropshipPrice = $request->dropship_price[$idx] ?? 0;
                if ($productDropshipPrice == $productOriginPrice)
                    $productDiscount = 0;
                else
                    $productDiscount = $productOriginPrice - $productDropshipPrice;

                $subTotal += $productOriginPrice * $orderQty;
                $discountTotal += $productDiscount * $orderQty;

                $totalProductWeight += $productWeight * $orderQty;

                $orderManagementDetailData = new orderManagementDetail();
                $orderManagementDetailData->order_management_id = $orderManagementId;
                $orderManagementDetailData->product_id = $productId;
                $orderManagementDetailData->shop_id = $shop_id;
                $orderManagementDetailData->quantity = $orderQty;
                $orderManagementDetailData->price = $productOriginPrice;
                $orderManagementDetailData->discount_price = $productDiscount;
                $orderManagementDetailData->seller_id = $sellerId;
                $orderManagementDetailData->created_at = new DateTime();
                $orderManagementDetailData->save();


            }


            foreach ($request->shipping_method_id as $idx => $shippingMethodId) {
                $shippingCostId = $shippingMethodId;
                $shippingMethodName = $request->shipping_method_name[$idx] ?? '';
                $shippingMethodPrice = $request->shipping_method_price[$idx] ?? 0;
                $shippingMethodDiscount = $request->shipping_method_discount[$idx] ?? 0;
                $shippingMethodSelected = $request->shipping_method_selected[$idx] ?? 0;

                if ($shippingMethodSelected == CustomerShippingMethod::IS_SELECTED_YES) {
                    $shippingCostTotal += $shippingMethodPrice;
                    $discountTotal += $shippingMethodDiscount;
                }

                $isNewStatus = CustomerShippingMethod::IS_NEW_STATUS_NO;
                if (empty($shippingMethodId)) {
                    $isNewStatus = CustomerShippingMethod::IS_NEW_STATUS_YES;

                    $shippingCostData = new ShippingCost();
                    $shippingCostData->shipper_id = 0;
                    $shippingCostData->name = $shippingMethodName;
                    $shippingCostData->weight_from = 0;
                    $shippingCostData->weight_to = 0;
                    $shippingCostData->price = $shippingMethodPrice;
                    $shippingCostData->created_at = new DateTime();
                    $shippingCostData->save();
                    $shippingCostId = $shippingCostData->id();

                }

                $customerShippingMethodData = new CustomerShippingMethod();
                $customerShippingMethodData->order_id = $orderManagementId;
                $customerShippingMethodData->shipping_cost_id = $shippingCostId;
                $customerShippingMethodData->price = $shippingMethodPrice;
                $customerShippingMethodData->discount_price = $shippingMethodDiscount;
                $customerShippingMethodData->is_selected = $shippingMethodSelected;
                $customerShippingMethodData->enable_status = CustomerShippingMethod::ENABLE_STATUS_YES;
                $customerShippingMethodData->is_new_status = $isNewStatus;
                $customerShippingMethodData->save();


            }

            $totalAmount = $subTotal + $shippingCostTotal - $discountTotal;


            $OrderManagementsDatas = OrderManagement::find($orderManagementId);
            $OrderManagementsDatas->sub_total = $subTotal;
            $OrderManagementsDatas->shipping_cost = $shippingCostTotal;
            $OrderManagementsDatas->in_total = $totalAmount;
            $OrderManagementsDatas->total_product_weight = round($totalProductWeight);
            $OrderManagementsDatas->update();

            DB::commit();

            if ($request->payment_method == OrderManagement::PAYMENT_METHOD_BANK_TRANSFER) {
                $orderManagement = OrderManagement::where('id', $orderManagementId)->first();

                $orderManagement->payment_method = OrderManagement::PAYMENT_METHOD_BANK_TRANSFER;
                $orderManagement->order_status = OrderManagement::ORDER_STATUS_PENDING_PAYMENT;
                $orderManagement->save();
            }


            if ($request->payment_method == OrderManagement::PAYMENT_METHOD_INSTANT) {
                $userAgent = new Agent();

                $orderManagement = OrderManagement::where('id', $orderManagementId)
                    ->with('shop')
                    ->first();

                $ksherPay = new KsherPay($this->ksherAppId(), $this->ksherPrivateKey());

                $paymentOrderNumber = $orderManagementId . '-' . strtotime(now());
                $productName = $orderManagement->shop->name . ' Order';
                $totalFee = round($orderManagement->in_total, 2) * 100;

                $device = 'PC';
                if ($userAgent->isMobile()) {
                    $device = 'h5';
                }

                $paymentData = [
                    'mch_order_no' => $paymentOrderNumber,
                    'total_fee' => $totalFee,
                    'fee_type' => 'THB',
                    'channel_list' => 'bbl_promptpay,wechat,alipay,truemoney,airpay,linepay,ktbcard',
                    'mch_code' => $paymentOrderNumber,
                    'mch_redirect_url' => route('order-status', ['order_id' => $orderId]),
                    'mch_redirect_url_fail' => route('order-management.public-url', ['order_id' => $orderId, 'status' => 'failed']),
                    'product_name' => $productName,
                    'refer_url' => config('app.url'),
                    'device' => $device,
                    'logo' => $orderManagement->shop->logo_url,
                    'time_stamp' => date('YmdHis')
                ];

                $paymentResponse = json_decode($ksherPay->gateway_pay($paymentData), true);

                if($paymentResponse){
                     $paymentUrl = $paymentResponse['data']['pay_content'];
                }
                else{
                     $paymentUrl = '';
                }

                if (!empty($paymentUrl)) {
                    $orderManagement = OrderManagement::where('id', $orderManagementId)->first();

                    $orderManagement->payment_url = $paymentUrl;
                    $orderManagement->payment_method = OrderManagement::PAYMENT_METHOD_INSTANT;
                    $orderManagement->order_status = OrderManagement::ORDER_STATUS_PENDING_PAYMENT;
                    $orderManagement->place_order_time = date('Y-m-d H:i:s');
                    $orderManagement->sign = $paymentResponse['sign'];
                    $orderManagement->save();

                } elseif (empty($paymentUrl)) {
                    return $this->apiResponse(Response::HTTP_INTERNAL_SERVER_ERROR, 'Payment failed');
                }
            }

            $responseData = [
                'orderId' => $orderManagementId,
                'publicUrl' => route('order-management.public-url', [ 'order_id' => $orderId ]),
            ];

            return $this->apiResponse(Response::HTTP_CREATED, 'Order created.', $responseData);

        } catch (\Exception $th) {
            report($th);

            DB::rollBack();

            return $this->apiResponse(Response::HTTP_INTERNAL_SERVER_ERROR, 'Sorry, something went wrong. ' . $th->getMessage());
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function edit($id)
    {
        $sellerId = Auth::user()->id;

        $orderManagement = OrderManagement::where('id', $id)
                            ->where('seller_id', $sellerId)
                            ->with('shop')
                            ->with('channels')
                            ->with('customer')
                            ->with(['order_management_details' => function($detail) {
                                $detail->with('product');
                            }])
                            ->with(['customer_shipping_methods' => function($shippingMethod) {
                                $shippingMethod->with(['shipping_cost' => function($shippingCost) {
                                    $shippingCost->with('shipper');
                                }]);
                            }])
                            ->first();

        //dd($orderManagement);

        abort_if(!$orderManagement, 404, __('translation.Data not found'));

        if ($orderManagement->customer_type == 1)
            $channels = Channel::where('name', 'Dropshipper')->first();
        else
            $channels = Channel::where('seller_id', $sellerId)->where('display_channel', 1)->orderBy('name')->get();

        $products = Product::where('seller_id', Auth::user()->id)->get();

        $enabledShippingMethodIds = [];
        foreach ($orderManagement->customer_shipping_methods as $shippingMethod) {
            array_push($enabledShippingMethodIds, $shippingMethod->shipping_cost_id);
        }


        $totalProductWeight = $orderManagement->total_product_weight;

        $defaultshippingCostsByWeight = ShippingCost::filterWeightBetween($totalProductWeight)
                                        ->with('shipper')
                                        ->whereHas('shipper', function(Builder $shipper) use ($sellerId) {
                                            $shipper->where('seller_id', $sellerId);
                                        })
                                        ->orderBy('name', 'asc')
                                        ->get();

        $availableShippingCosts = $defaultshippingCostsByWeight->filter(function($shippingCost) use ($enabledShippingMethodIds) {
            return !in_array($shippingCost->id, $enabledShippingMethodIds);
        });


        $addedProductCodes = $orderManagement->order_management_details->map(function($detail) {
            return $detail->product->product_code;
        });

        $manualPaymentSum = OrderManagement::getManualPaymentSum($id);
        $manualRefundedSum = OrderManagement::getmanualRefundedSum($id);
        $paymentDetailsAllManual = PaymentManual::where('order_id',$id)->get();
        $paymentDetailsOthers = Payment::where('order_id', $id)->first();

        $getOrderStatus = OrderManagement::getOrderStatus($orderManagement->order_status);

        $data = [
            'manualPaymentSum' => $manualPaymentSum,
            'manualRefundedSum' => $manualRefundedSum,
            'paymentDetailsAllManual' => $paymentDetailsAllManual,
            'paymentDetailsOthers' => $paymentDetailsOthers,
            'getOrderStatus' => $getOrderStatus,
            'channels' => $channels,
            'orderStatuses' => OrderManagement::getAllAvailableStatusForEdit(),
            'paymentStatuses' => OrderManagement::getAllPaymentStatus(),
            'products' => ProductTypeAheadResource::collection($products),
            'orderManagement' => $orderManagement,
            'availableShippingCosts' => $availableShippingCosts,
            'addedProductCodes' => $addedProductCodes,
            'isSelectedShippingMethod' => CustomerShippingMethod::IS_SELECTED_YES,
            'taxEnableValues' => OrderManagement::getAllTaxEnableValues(),
            'taxEnableYes' => OrderManagement::TAX_ENABLE_YES,
            'taxRateSetting' => TaxRateSetting::where('seller_id', $sellerId)->first(),
            'customerType' => $orderManagement->customer_type
        ];

        return view('seller.order_management.edit', $data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\OrderManagement\UpdateRequest  $request
     * @param  \App\Actions\OrderManagement\CalculateTotalAmountUpdateAction  $calculateTotalAmount
     * @return Response
     * @throws \Exception
     */
    public function update(UpdateRequest $request, CalculateTotalAmountUpdateAction $calculateTotalAmount)
    {
        try {
            $sellerId = Auth::user()->id;

            $orderManagementId = $request->id;
            $order = OrderManagement::where('id', $orderManagementId)->first();

            $customersTable = (new Customer())->getTable();
            $orderManagementsTable = (new OrderManagement())->getTable();
            $orderManagementDetailsTable = (new OrderManagementDetail())->getTable();
            $shippingCostsTable = (new ShippingCost())->getTable();
            $customerShippingMethodsTable = (new CustomerShippingMethod())->getTable();
            $taxRateSettingTable = (new TaxRateSetting())->getTable();


            DB::beginTransaction();

            $customerData = [
                'customer_name' => $request->customer_name,
                'contact_phone' => $request->contact_phone,
                'seller_id' => $sellerId,
                'order_type' => Customer::ORDER_TYPE_DEFAULT
            ];

            $customer = Customer::where($customerData)->first();
            if(!empty($customer->id)){
                $customerId = $customer->id;
            }
            else{
                $customerId = 0;
            }
            if (empty($customerId)) {
                $customerData['created_at'] = new DateTime();
                $customerId = DB::table($customersTable)->insertGetId($customerData);
            }

            if ($request->customer_type != 1){
                $shopId = $request->shop_id;
            }
            else{
                $shopId = $order->shop_id;
            }

            $taxRate = 0;
            if ($request->tax_enable == OrderManagement::TAX_ENABLE_YES) {
                $taxRateSetting = DB::table($taxRateSettingTable)->where('seller_id', $sellerId)->first();
                $taxRate = $taxRateSetting->tax_rate ?? 0;
            }
            $tax_vat_amount = $request->tax_vat_amount;

            $subTotal = 0;
            $shippingCostTotal = 0;
            $discountTotal = 0;
            $totalAmount = 0;
            $inTotalDiscount = 0;

            $totalProductWeight = 0;

            DB::table($orderManagementDetailsTable)
                ->where('order_management_id', $orderManagementId)
                ->delete();

            foreach ($request->product_id as $idx => $productId) {
                $productOriginPrice = $request->product_price[$idx] ?? 0;
                $productDiscount = $request->product_discount[$idx] ?? 0;
                $productWeight = $request->product_weight[$idx] ?? 0;
                $orderQty = $request->product_qty[$idx] ?? 0;

                $subTotal += $productOriginPrice * $orderQty;
                $discountTotal += $productDiscount * $orderQty;

                if($productDiscount == 0){
                    $inTotalDiscount += 0;
                }
                else{
                    $inTotalDiscount += ($productOriginPrice - $productDiscount)*$orderQty;
                }

                $totalProductWeight += $productWeight * $orderQty;

                $orderManagementDetailData = [
                    'order_management_id' => $orderManagementId,
                    'product_id' => $productId,
                    'shop_id' => $shopId,
                    'quantity' => $orderQty,
                    'price' => $productOriginPrice,
                    'discount_price' => $productDiscount,
                    'seller_id' => $sellerId,
                    'created_at' => new DateTime()
                ];

                DB::table($orderManagementDetailsTable)->insert($orderManagementDetailData);
            }


            DB::table($customerShippingMethodsTable)
                ->where('order_id', $orderManagementId)
                ->delete();

            foreach ($request->shipping_method_id as $idx => $shippingMethodId) {
                $shippingCostId = $shippingMethodId;
                $shippingMethodName = $request->shipping_method_name[$idx] ?? '';
                $shippingMethodPrice = $request->shipping_method_price[$idx] ?? 0;
                $shippingMethodDiscount = $request->shipping_method_discount[$idx] ?? 0;
                $shippingMethodSelected = $request->shipping_method_selected[$idx] ?? 0;

                if ($shippingMethodSelected == CustomerShippingMethod::IS_SELECTED_YES) {
                    $shippingCostTotal += $shippingMethodPrice;
                    $discountTotal += $shippingMethodDiscount;
                }

                $isNewStatus = CustomerShippingMethod::IS_NEW_STATUS_NO;
                if (empty($shippingMethodId)) {
                    $shippingCostData = [
                        'shipper_id' => 0,
                        'name' => $shippingMethodName,
                        'weight_from' => 0,
                        'weight_to' => 0,
                        'price' => $shippingMethodPrice,
                        'created_at' => new DateTime()
                    ];

                    $isNewStatus = CustomerShippingMethod::IS_NEW_STATUS_YES;

                    $shippingCostId = DB::table($shippingCostsTable)->insertGetId($shippingCostData);
                }


                $customerShippingMethodData = [
                    'order_id' => $orderManagementId,
                    'shipping_cost_id' => $shippingCostId,
                    'price' => $shippingMethodPrice,
                    'discount_price' => $shippingMethodDiscount,
                    'is_selected' => $shippingMethodSelected,
                    'enable_status' => CustomerShippingMethod::ENABLE_STATUS_YES,
                    'is_new_status' => $isNewStatus,
                    'created_at' => new DateTime()
                ];

                DB::table($customerShippingMethodsTable)->insert($customerShippingMethodData);
            }


            $totalAmount = $calculateTotalAmount->handle($subTotal, $tax_vat_amount, $shippingCostTotal, $inTotalDiscount);

            DB::table($orderManagementsTable)
                ->where('id', $orderManagementId)
                ->update([
                    'shipping_name' => $request->shipping_name,
                    'shipping_phone' => $request->shipping_phone,
                    'shipping_address' => $request->shipping_address,
                    'shipping_district' => $request->shipping_district,
                    'shipping_sub_district' => $request->shipping_sub_district,
                    'shipping_province' => $request->shipping_province,
                    'shipping_postcode' => $request->shipping_postcode,
                    'tax_enable' => $request->tax_enable,
                    'tax_invoice_note' => $request->tax_invoice_note,
                    'tax_number' => $request->tax_number,
                    'company_phone_number' => $request->company_phone_number,
                    'company_contact_name' => $request->company_contact_name,
                    'company_name' => $request->company_name,
                    'company_address' => $request->company_address,
                    'company_province' => $request->company_province,
                    'company_district' => $request->company_district,
                    'company_sub_district' => $request->company_sub_district,
                    'company_postcode' => $request->company_postcode,
                    //'order_status' => $request->order_status,
                    'sub_total' => $subTotal,
                    //'tax_rate' => $taxRate,
                    'tax_rate' => $request->tax_vat_amount,
                    'shipping_cost' => $shippingCostTotal,
                    'in_total' => $totalAmount,
                    'total_product_weight' => round($totalProductWeight),
                    'updated_at' => new DateTime()
                ]);

            if ($request->customer_type != 1){
                $order->shop_id = $shopId;
                $order->channel_id = $request->channel_id;
                $order->customer_id = $customerId;
                $order->contact_name = $request->contact_name;
                $order->update();
            }

            DB::commit();

            $responseData = [
                'subTotal' => $subTotal,
                'shippingCost' => $shippingCostTotal,
                'discountTotal' => $discountTotal,
                'totalAmount' => $totalAmount
            ];

            return $this->apiResponse(Response::HTTP_CREATED, 'Order successfully updated.', $responseData);

        } catch (\Exception $th) {
            report($th);

            DB::rollBack();

            return $this->apiResponse(Response::HTTP_INTERNAL_SERVER_ERROR, 'Sorry, something went wrong. ' . $th->getMessage());
        }
    }

    public function orderManagementUpdate(Request $request, $id)
    {
        if ($request->customer_id_main == 0) {
            $customer = new Customer();
            $customer->contact_phone = $request->contact_phone_main;
            $customer->customer_name = $request->customer_name_main;
            $customer->seller_id = Auth::id();
            $customer->order_type = 1;
            $customer->save();
        } else {
            $customer = Customer::find($request->customer_id_main);
            $customer->contact_phone = $request->contact_phone_main;
            $customer->customer_name = $request->customer_name_main;
            $customer->order_type = 1;
            $customer->update();
        }

        $channel = Channel::where('name', $request->channel_main)->first();

        $orderManagement = OrderManagement::find($id);
        if ($channel)
        $orderManagement->channel_id = $channel->id;
        $orderManagement->shop_id = $request->shop_id;

        $orderManagement->customer_id = $customer->id;
        $orderManagement->contact_name = $request->contact_name_main;
        $orderManagement->shipping_name = $request->shipping_name_main;
        $orderManagement->shipping_phone = $request->shipping_phone_main;
        $orderManagement->shipping_address = $request->shipping_address_main;

        $orderManagement->shipping_district = $request->dis_main;
        $orderManagement->shipping_sub_district = $request->sub_main;
        $orderManagement->shipping_province = $request->pro_main;
        $orderManagement->shipping_postcode = $request->postcodes_main;

        $orderManagement->shipping_methods = $request->shipping_methods;
        $orderManagement->order_status = $request->order_status;
        $orderManagement->payment_status = $request->payment_status;
        $orderManagement->total_discount = $request->total_discount;
        $orderManagement->total_product_weight = $request->total_product_weight;
        $orderManagement->created_by = Auth::user()->id;
        $orderManagement->save();

        OrderManagementDetail::where('order_management_id',$orderManagement->id)->where('seller_id',Auth::user()->id)->delete();

        if(count($request->product_id)>0)
        {
            foreach($request->product_id as $key=>$row)
            {
                $orderManagementDetails = new OrderManagementDetail();
                $orderManagementDetails->product_id = $row;
                $orderManagementDetails->quantity = $request->product_quantity[$key];
                //$orderManagementDetails->shop_id = $request->shop_id[$key];
                $orderManagementDetails->discount_price = $request->product_discount[$key];
                $orderManagementDetails->seller_id = Auth::user()->id;
                $orderManagementDetails->order_management_id = $orderManagement->id;
                $orderManagementDetails->save();
            }
        }

        if ($orderManagement) {
            return redirect('order_management')->with('success','Your Order Updated Successfully');
        }

        return redirect('order_management')->with('danger','Something happened wrong');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id)
    {
        //
    }

    public function orderManagementDelete($id)
    {
        $orderPurchase = OrderManagement::where('id',$id)->where('seller_id',Auth::user()->id)->delete();
        OrderManagementDetail::where('order_management_id',$id)->where('seller_id',Auth::user()->id)->delete();

        if ($orderPurchase) {
            return redirect()->back()->with('success','Order Purchase Deleted Successfully');
        }

        return redirect()->back()->with('danger','Something happened wrong');
    }

    public function orderManagementCancel(Request $request)
    {
        try {
            $orderManagement = OrderManagement::where('id', $request->id)->first();
            $orderManagement->order_status = OrderManagement::ORDER_STATUS_CANCEL;
            $orderManagement->save();

            return [
                'status' => 1
            ];

        } catch (\Exception $th) {
            report($th);

            return $this->apiResponse(Response::HTTP_INTERNAL_SERVER_ERROR, 'Something went wrong');
        }
    }

    public function getOrderedProducts(Request $request)
    {
        $orderId = $request->get('orderId', 0);
        $shipmentId = $request->get('shipmentId', 0);

        $orderDetails = OrderManagementDetail::where('order_management_id', $orderId)->with('product')->get();
        $shipments = ShipmentProduct::where('shipment_id', $shipmentId)->get();

        $productData = [];

        foreach ($orderDetails as $item) {

            $row = [];
            $row[] = '
                <div class="grid grid-cols-1 gap-4">
                    <div>
                        <img src="'. $item->product->image_url .'" class="w-16 md:w-11/12 h-auto" />
                    </div>
                    <div>
                        <span class="whitespace-nowrap text-blue-500">
                            ID : <strong>'. $item->product->id .'</strong>
                        </span>
                    </div>
                </div>
            ';

            //$productPrice = $item->price - $item->discount_price;
            $productPrice = '';
            if(empty($item->discount_price) || $item->discount_price == NULL){
                    $productPrice = $item->product->price;
            }
            else{
                $productPrice = $item->discount_price;
            }

            $shippedProducts = $item->quantity;
            foreach ($shipments as $shipment){
                if ($shipment->product_id == $item->product_id){
                    $shippedProducts = $shipment->quantity;
                }
            }

            $shippedProductsContents = '';
            if (!empty($shipmentId)){
                $shippedProductsContents = '
                    <div class="mb-1">
                        <div class="whitespace-nowrap">
                            <label class="text-gray-700">
                                Shipment Qty :
                            </label>
                            <span class="text-gray-900">
                                '. number_format($shippedProducts) .'
                            </span>
                        </div>
                    </div>
                    ';
            }

            $row[] = '
                <div>
                    <div class="mb-1">
                        <strong>'. $item->product->product_name .'</strong>
                    </div>
                    <div class="mb-1">
                        <strong class="text-blue-500">'. $item->product->product_code .'</strong>
                    </div>
                    <div class="mb-1">
                        <div class="whitespace-nowrap">
                            <label class="text-gray-700">
                                Price :
                            </label>
                            <span>'. currency_symbol('THB') . number_format(floatval($productPrice), 2) .'</span>
                        </div>
                    </div>
                    <div class="mb-1">
                        <div class="whitespace-nowrap">
                            <label class="text-gray-700">
                                Ordered Qty :
                            </label>
                            <span class="text-gray-900">
                                '. number_format($item->quantity) .'
                            </span>
                        </div>
                    </div>
                   ' . $shippedProductsContents . '
                    <div class="mb-1">
                        <div class="whitespace-nowrap">
                            <label class="text-gray-700">
                                Total Price :
                            </label>
                            <strong class="">'. currency_symbol('THB') . number_format(floatval($productPrice * $shippedProducts), 2) .'</strong>
                        </div>
                    </div>
                </div>
            ';

            $productData[] = $row;
        }

        return response()->json([
            'data' => $productData,
        ]);
    }

    public function getShippingAddress(Request $request)
    {
        $orderId = $request->get('orderId', 0);

        $order = OrderManagement::where(['id' => $orderId])->first();

        $data = '';
        if ($order){
            $data = '
                <div>
                    <div class="mb-2 pl-3">
                        <span class="font-bold">'.  strtoupper('Shipping To :') .'</span>
                    </div>
                    <div class="w-full overflow-x-auto">
                        <table class="w-full table table-borderless">
                            <tr>
                                <td class="text-gray-700">Cust. Name</td>
                                <td class="text-blue-600 font-semibold">'.  strtoupper($order->customer->customer_name) .'</td>
                            </tr>
                            <tr>
                                <td class="text-gray-700 align-content-start">Address</td>
                                <td class="">
                                    <span>'. $order->shipping_address .'</span> <br>
                                    <span>'. $order->shipping_sub_district .', </span>
                                    <span>'. $order->shipping_district .',</span> <br>
                                    <span>'. $order->shipping_province .', </span>
                                    <span>'. $order->shipping_postcode .'</span>
                                </td>
                            </tr>
                            <tr>
                                <td class="text-gray-700">Phone No.</td>
                                <td class="font-bold">'. $order->shipping_phone .'</td>
                            </tr>
                        </table>
                    </div>
                </div>
            ';
        }

        return [
            'data' => $data,
        ];
    }

    /**
     * @TODO put private key in a file and read it here
     */
    public function private_key(){
        $privatekey=<<<EOD
    -----BEGIN RSA PRIVATE KEY-----
    MIICYgIBAAKBgQCPzwGZv5sCMwf8Sv+FXUqrULSEdeB846z2OCnPw+ynDTUqApRz
    0Goj1gYaK5Gu4vLxTH06PpL96sAB9C0pACBz3xewotdAwoHK0B86TaWk0bt4+jSL
    HMAvgLOF2DH5uAlDzYp8KtQAyhXOowds/20POw+Q3m2RgLCMXQ4OzElp8QIDAQAB
    AoGBAI4VecBdZhp7LwWfV+x9axvuRhyllmHuVOKERRNIwZWfYAqct+3hWi0D9c1/
    hJWlF2E/MG8Oig6kFIcZp5OwAvIHsEkJjryQSk4qERpuU99TG9u5ayGmFUPaC0x6
    fzgEw3+ANYOytWTfsxGbUL1SFoZ1yqKD/iKuBE2BXgM6fZbBAkUAv3jyTVA5R+kg
    B3eFSu+hywi87Q2zZ+myBHGBC4Zb3mhmKRoiBMGZS40y9JXNsmrx3IhynQDSiywJ
    7DyX+Bo7SJ90eykCPQDAReuuYuU/wqcqtnscRzVCW9aydquaDYUHOUXWsAGdghtK
    SJFJW717RLHO/3L230f2pl5TBfPG3hGYmYkCRA8O0e9mmbqgCNbNfXwRMGYpP8Jc
    y3kmlctnqcBgRqVNDIu69GXvW8DnT9SQW2bmpjKzwF+8itJLGlSrxz/JwFPLxntR
    Aj0AjT1PqaSAHtxQjDHMMbOlTf/EsQg3ekzgIbRStyhHp3qBrYmtICRCBqEptJM1
    0l+mr2r68yX2M2nBp0VxAkUAkT6IL2UAbBi5mTK2YgakqyWCcFsLg7fGtArKcNiF
    QssbrooyyUHq8GKQ/4IYQO6M80xTf6vY3r3Gxs8LkqoQirHwRN0=
    -----END RSA PRIVATE KEY-----
    EOD;

    return $privatekey;
    }

    /**
     * @TODO put private key in a file and read it here
     */
    public function createPayment($fee,$order_id)
    {
        $appid='mch20027';
        $appid='mch37545';
        $privatekey=<<<EOD
        -----BEGIN RSA PRIVATE KEY-----
        MIICYgIBAAKBgQCMYnaaVSBx4FYWnF5hKfgHvtwP5NdZv/bh88VqKe9g+A2TggSw
        3EK2qxN7eGbqNh/omI6LjE9nyNJE+Z0EIXbQV9sNiTaDZDODqCklA0dOTblVNiMj
        IBpjcXPA1iMNzBPMZjP//iBVXlSPsQUyExuHy9nzcX4BRwxOA5ZIjrhEQwIDAQAB
        AoGBAIxW8pIef6zXw7ge4groVdgIaR5Key5xxXDkrXoQKgoacBgCZoYX62mJZJSO
        LPP+3686s2W2AruR+wKRNg9hoxshy3AoNN7wIzcQnyDSrdv+85onATvAMlEhgY4X
        rHXTkCi0bMg+h/QojExhLdROKLUQKriIKVQlkF7IWbFLzOcBAkUA41c0hmUthgZd
        OqFdanoiPFRTRZcI9CmtTNFjXeunX+0psQQMOzweV+W/XzUliBhFFv3UVy34tMLX
        IsmlvsvFqkFxAGMCPQCeFP3IjOs7f1jqoU3MliYAcmF6a3XwdCE/NLzgtdxeGRXk
        kcDWa78Y5IkHOGWdEKgsgJ8srlmGxuL0wqECRETqm66eH1XAuiRa5HGxwo0dVv0C
        kyFJPRLLat7+4AdRYtEZlAek6uHkcMYQ22bNTKxymBsxgXJymjsee9NB/JLnMbVX
        Aj0AnMbsuk0nriYqJOg8pD31ClRl4Gda3FIP9wNyntk96ASw9bKnsP/C0gk07Pg9
        rnuqjhgxxLpVB5mP1HaBAkUAvJVuf6g4JHf92A/VqnMHE9VrUAIqzQSjEbJyhFlX
        3SYADWlMo4tYETLfzdYC3c9OvwVD3fhs38fgO+LtbsANu9ky/d0=
        -----END RSA PRIVATE KEY-----
        EOD;
        set_time_limit(0);
        $class = new KsherPay($appid, $privatekey);

        $order_id_with =  Crypt::encryptString($order_id);
        $gateway_pay_data = array('mch_order_no'=>$order_id,
            "total_fee" => round($fee, 2)*100,
            "fee_type" => 'THB',
            // "channel_list" => 'bbl_promptpay,wechat,alipay,truemoney,airpay,linepay,ktbcard',
            "channel_list" => 'bbl_promptpay,wechat,alipay,truemoney,airpay,linepay,ktbcard.e-wallet',
            'mch_code' => $order_id,
            'mch_redirect_url' => url('order_status/'.$order_id_with),
            'mch_redirect_url_fail' => 'http://www.ksher.com',
            'product_name' => 'Dodostock product order',
            'refer_url' => 'http://dodostock/public',
            'device' => 'PC');
        $gateway_pay_response = $class->gateway_pay($gateway_pay_data);
        $gateway_pay_array = json_decode($gateway_pay_response, true);

        if (isset($gateway_pay_array['data']['pay_content'])){
            return $gateway_pay_array['data']['pay_content'];
        }else{
            // return NULL;
            echo "<pre>";
            print_r($gateway_pay_response);
        }
        exit();
    }


    public function orderStatus($order_id)
    {
         $orderManagement = OrderManagement::where('order_id',$order_id)->first();
         $appid='mch37567';
         $privatekey = $this->private_key();
         set_time_limit(0);

         $class = new KsherPay($appid, $privatekey);
         $order_query_request_param = array(
            'mch_order_no'=>$orderManagement->id,
            'appid'=>$appid,
        );

        $gateway_pay_response = $class->gateway_order_query($order_query_request_param);
        $gateway_pay_array = json_decode($gateway_pay_response, true);

        if(isset($gateway_pay_array['code']) && $gateway_pay_array['code'] == 0 && $gateway_pay_array['data']['result'] == 'SUCCESS'){

        $orderManagement->payment_status = 1;
        $orderManagement->payment_channel_from_ksher = $gateway_pay_array['data']['channel'];
        $orderManagement->order_status = 2;
        $orderManagement->payment_date  = $gateway_pay_array['data']['time_end'];
        $result = $orderManagement->save();

        return Redirect::to('/orders/'.$order_id)->with('success', 'Your Order Updated Successfully');
        }
    }

    public function getSubCatName(){
        $subCategoryID = $_GET['subCategoryID'];
        $cats = Category::select('cat_name')->where('id',$subCategoryID)->first();
        if($cats){
            return array(
                'cat_name'=>$cats->cat_name
            );
        }
        else{
            return 0;
        }
    }

    /**
     * @TODO remove commented code
     */
    public function check_customer_phone(){
        $customer_phone = $_GET['customer_phone'];

        $customers_details = Customer::where('contact_phone', $customer_phone)->first();
        if(!empty($customers_details->contact_phone)){

           $customers_orders_details = OrderManagement::where('customer_id', $customers_details->id)->first();
            if (!empty($customers_orders_details)) {
                $contact_name = $customers_orders_details->contact_name;
                if ($customers_orders_details->channels)
                    $channel =  $customers_orders_details->channels->id;
            }
            else {
                $contact_name = '';
                $channel = '';
            }

            return array(
                'customer_id' => $customers_details->id,
                'customer_name'=> $customers_details->customer_name,
                'channel'=> $channel,
                'contact_name'=> $contact_name
            );
        }
        else{
            return 0;
        }
    }

    public function bulkStatus(Request $request)
    {
        if ($request->ajax()) {

            if (isset($request->jSonData) && $request->jSonData != null) {
                $arr = json_decode($request->jSonData);
                foreach ($arr as $webIDOrderID) {
                    $orderManagement = OrderManagement::find($webIDOrderID);
                    $orderManagement->order_status = $request->status;
                    $orderManagement->save();
                    $flag = 1;
                }

                if ($flag==1) {
                    echo 'Order Status Changed Successfully';
                }
            }
        }
    }

    /**
     * @TODO remove commented code
     * @TODO refactor DB::table(), use Model
     * @TODO missing return statement, return void
     */
    public function getAllSubCatgeory(Request $request){

        if ($request->ajax()) {
            if (isset($request->categoryID) && $request->categoryID != null && $request->categoryID != 'all') {
                $categoryID = $request->categoryID;
                $data = DB::table('products')
                    ->leftjoin('categories', 'products.category_id', '=', 'categories.id')
                    ->select('products.product_name','categories.*')
                    ->groupBy('products.category_id')
                    ->where('categories.parent_category_id', $categoryID)
                    ->get();
            }
            else{

                $data = DB::table('products')
                    ->leftjoin('categories', 'products.category_id', '=', 'categories.id')
                    ->select('products.product_name','categories.*')
                    ->groupBy('products.category_id')
                    ->where('categories.parent_category_id', '!=', 0)
                    ->get();
            }


            return Datatables::of($data)
                ->addIndexColumn()
                ->addColumn('image', function ($row) {
                    $image_loc = asset($row->image);
                    $no_image = asset('img/No_Image_Available.jpg');
                    if(!empty($row->image)){
                        return '<img style="text-align:center;" class="text-center" src="'.$image_loc.'" width="60" height="60">';
                    }
                    else{
                        return '<img style="text-align:center;" class="text-center" src="'.$no_image.'" width="60" height="60">';
                    }
                })
                ->addColumn('cat_name', function ($row) {
                    return $row->cat_name;
                })
                ->addColumn('action', function ($row) {
                    return '<a  class="modal-open bg-green-500 text-white rounded px-2 py-1 mr-1 capitalize cursor-pointer subCat" data-id="' . $row->id . '" >Select</a>';
                })
                ->rawColumns(['image','action', 'cat_name'])
                ->make(true);
        }

    }

    /**
     * @TODO remove commented code
     * @TODO refactor DB::table(), use Model
     * @TODO $subCategoryID is undefined
     * @TODO missing return statement, return void
     */
    public function getAllProductCatWise(Request $request){

        if ($request->ajax()) {
            if (isset($request->subCategoryID) && $request->subCategoryID != null && $request->subCategoryID != 'all') {
                $subCategoryID = $request->subCategoryID;
                $data = Product::where('category_id',$subCategoryID)->get();
            }else{
                //$data = OrderManagement::where('seller_id',Auth::user()->id)->get();
                $data = Product::where('category_id', $subCategoryID)->get();
            }

            //$data = OrderManagement::latest()->get();
            return Datatables::of($data)
                ->addIndexColumn()
                ->addColumn('product_details', function ($row) {
                    $image_loc = asset($row->image);
                    $no_image = asset('img/No_Image_Available.jpg');

                    if(!empty($row->image)){
                        $product_image =  $image_loc;
                    }
                    else{
                        $product_image =  $no_image;
                    }

                    return '
                <div style="float:left; width:21%; marging-right:20px !important;"><img src="'.$product_image.'" width="70" height="70" style="marging-right:25px;"></div>
                <div style="padding-left:38px !important; text-align:left; float:left; width:75%">
                 <strong>ID:</strong>'.$row->id.'<br>
                 '.$row->product_name.'<br>
                 '.$row->product_code.'<br>
                 <strong style="padding-bottom:5px;">Price:</strong>'.$row->price.'<br><br>
                 <a  class="modal-open bg-green-500 text-white rounded px-2 py-1 mr-1 capitalize cursor-pointer add_product_to_cart" data-id="' . $row->product_name . ' ('.$row->product_code.')" >Add to Cart</a>
                </div>';
                })
                ->addColumn('action', function ($row) {
                    // return '
                    // <a  class="modal-open bg-green-500 text-white rounded px-2 py-1 mr-1 capitalize cursor-pointer subCat" data-id="' . $row->id . '" >Select</a>

                    // ';
                })
                ->rawColumns(['product_details','action'])
                ->make(true);
        }
    }

    /**
     * @TODO refactor DB::table(), use Model
     */
    public function get_all_shipping_methods(){
        $totalWeights = $_GET['totalWeights'];

        $shippers =  DB::table('shipping_costs')
            ->join('shippers', 'shipping_costs.shipper_id', '=', 'shippers.id')
            ->select('shipping_costs.id','shipping_costs.name', 'shipping_costs.price', 'shippers.name as shippers_name')
            ->where('shipping_costs.weight_from', '<=', $totalWeights)
            ->where('shipping_costs.weight_to', '>=', $totalWeights)
            ->get();

        return view('seller.order_management.all_shippers', compact('shippers'));
    }

    public function getOrderHistory(Request $request){
        $editData = OrderManagement::where('id',$request->order_id)->with('orderProductDetails')->where('seller_id',Auth::user()->id)->first();

        $data = [];
        if(isset($editData->orderProductDetails) && count($editData->orderProductDetails)>0){
            $priceAndShop = [];
            foreach($editData->orderProductDetails as $key=>$row)
            {
                $price = ProductPrice::where('seller_id',Auth::user()->id)->where('product_id',$row->product->id)->where('shop_id',$row->shop_id)->first();

                if(!empty($price->price))
                {
                    $priceAndShop[$key]['price'] = $price->price;
                }
                else
                {
                    $priceAndShop[$key]['price'] = '';
                }
                if(!empty($price->shop_id))
                {
                    $priceAndShop[$key]['shop'] = $price->shop_id;
                }
                else
                {
                    $priceAndShop[$key]['shop'] = '';
                }
                if(!empty($row->shop_id))
                {
                    $getShopId = 'shop_id'.$row->shop_id;
                }
                else{
                    $getShopId = '';
                }

                array_push($data,$row->product->product_code.$getShopId);
            }
        }
        Session::put('itemArray',$data);
        return view('seller.order_management.getOrderHistory', compact('editData', 'priceAndShop'));
    }

    public function createShipment(Request $request){
        $shipments = new Shipment();
        $shipments->order_id = $request->order_id;
        $shipments->shipment_date  = Carbon::parse($request->shipment_date)->format('Y-m-d');
        $shipments->shipment_status = Shipment::SHIPMENT_STATUS_READY_TO_SHIP;
        $shipments->seller_id = Auth::user()->id;
        $result = $shipments->save();
        if($result)
        {
            $orderManagement = OrderManagement::find($request->order_id);
            $orderManagement->order_status = Shipment::SHIPMENT_STATUS_READY_TO_SHIP;
            $orderManagement->save();
            $qrcode = 'ACS'.$shipments->id;
            QrCode::generate($qrcode, public_path('public/shipments_qrcodes/' . $qrcode . '.svg'));
            return redirect('order_management')->with('success','Your Shipment Created Successfully');
        }
        else{
            return redirect('order_management')->with('danger','Something happened wrong');
        }
    }

    /**
     * @TODO Go through the statements, remove unused or commented codebase
     * @TODO refactor code, a lot of static strings used here, use ENV
     * @TODO not sure why you're detecting mobile usage, refactor or make helper function for that
     * @TODO set_time_limit(0) is inefficient, shouldn't be used at all
     * @TODO if your execution requires time, you can queue it. Refactor set_time_limit
     */
    public function makeOrderPayment(Request $request){

    if(empty($request->shipping_name_main)){
        return Redirect::back()->with('danger','Shipping Address must be filled up');
    }
    if(empty($request->payment_method)){
        return Redirect::back()->with('danger','You need to select payment method');
    }

    if($request->payment_method == 2){
        if(isset($_SERVER['HTTP_USER_AGENT']) and !empty($_SERVER['HTTP_USER_AGENT'])){
           $user_ag = $_SERVER['HTTP_USER_AGENT'];
           if(preg_match('/(Mobile|Android|Tablet|GoBrowser|[0-9]x[0-9]*|uZardWeb\/|Mini|Doris\/|Skyfire\/|iPhone|Fennec\/|Maemo|Iris\/|CLDC\-|Mobi\/)/uis',$user_ag)){
               $device = 'h5';
           }
           else{
               $device = 'PC';
        }
    };

    $fee = 1;
    $appid='mch37567';
    $privatekey = $this->private_key();

    set_time_limit(0);
    $class = new KsherPay($appid, $privatekey);

    //$order_id_with =  Crypt::encryptString($request->order_id);
    $encryted_order_id = $request->encryted_order_id;
    $product_name = $request->shop_name.' order';

    $gateway_pay_data = array('mch_order_no'=>$request->order_id,
        "total_fee" => round($fee, 2)*100,
        "fee_type" => 'THB',
        "channel_list" => 'bbl_promptpay,wechat,alipay,truemoney,airpay,linepay,ktbcard',

        'mch_code' => $request->order_id,
        'mch_redirect_url' => url('order_status/'.$encryted_order_id),
         //'mch_redirect_url_fail' => 'http://www.ksher.com',
        'mch_redirect_url_fail' => url('orders/'.$encryted_order_id),
        'product_name' => $product_name,
        'refer_url' => 'http://dodostock/public',
        'device' => $device,
        'expire_time' => 5,
        'logo' =>$request->shop_logo,
        'time_stamp' =>date("YmdHis", time())
    );

    $gateway_pay_response = $class->gateway_pay($gateway_pay_data);
    $gateway_pay_array = json_decode($gateway_pay_response, true);


    if (isset($gateway_pay_array['data']['pay_content'])){


            $orderManagement = OrderManagement::where('id',$request->order_id)->first();
            $orderManagement->shipping_methods = $request->shipping_methodss;
            $orderManagement->shipping_cost = $request->shipping_cost;
            $orderManagement->in_total = $request->in_total;
            $orderManagement->shipping_name = $request->shipping_name_main;
            $orderManagement->shipping_phone = $request->shipping_phone_main;
            $orderManagement->shipping_address = $request->shipping_address_main;

            $orderManagement->place_order_time = Carbon::now()->format('Y-m-d H:i:s');
            $orderManagement->sign = $gateway_pay_array['sign'];
            $orderManagement->shipping_district = $request->dis_main;
            $orderManagement->shipping_sub_district = $request->sub_main;
            $orderManagement->shipping_province = $request->pro_main;
            $orderManagement->shipping_postcode = $request->postcodes_main;
            $orderManagement->save();


        return \Redirect::to($gateway_pay_array['data']['pay_content']);

        }else{
            print_r($gateway_pay_array);
        }
        exit();
    }

    if($request->payment_method == 1){

        if(empty($request->payment_date) || empty($request->payment_time) || empty($request->payment_slip)){
            return Redirect::back()->with('danger','Bank Details must be filled up');
        }
        //$order_id = $request->order_id;
        $orderPayments = new Payment();
        $orderPayments->order_id = $request->order_id;

        $orderPayments->amount = $request->in_total_inputs;
        //$orderPayments->payment_status = 1;
        $orderPayments->payment_method = 'Bank Transfer';

        $orderPayments->payment_date  = Carbon::parse($request->payment_date)->format('Y-m-d');

        $orderPayments->payment_time = $request->payment_time;
        $orderPayments->is_confirmed = 0;
        if ($request->hasFile('payment_slip')) {
                $upload = $request->file('payment_slip');
                // $file_type = $upload->getClientOriginalExtension();
                $upload_name =  time() . '_' . Str::random(10) . '_' . $upload->getClientOriginalName();
                $destinationPath = public_path('uploads/payment_slip');
                $upload->move($destinationPath, $upload_name);
                $orderPayments->payment_slip = 'uploads/payment_slip/'.$upload_name;
        }
        $orderPayments->save();

        return Redirect::back()->with('success','Your Order has been successfully Placed.');

    }


    }

    public function orderDelete(Request $request)
    {
        $OrderManagement = OrderManagement::where('id',$request->id)->delete();
        OrderManagement::where('order_id',$request->id)->where('seller_id',Auth::user()->id)->delete();
        if($OrderManagement)
        {
             return [
                'status' => 1
             ];
        }
     }

    /**
     * @TODO not sure why you're detecting mobile usage, refactor or make helper function for that
     *       - use jenssegers/agent package instead
     */
    public function isMobile(){
        return preg_match("/(android|avantgo|blackberry|bolt|boost|cricket|docomo|fone|hiptop|mini|mobi|palm|phone|pie|tablet|up\.browser|up\.link|webos|wos)/i", $_SERVER["HTTP_USER_AGENT"]);
    }

    /**
     * @TODO Go through the statements, remove unused or commented codebase
     * @TODO set_time_limit(0) is inefficient, shouldn't be used at all
     * @TODO if your execution requires time, you can queue it. Refactor set_time_limit
     */
    public function isPaymentSuccess(){
        $order_primary_id = $_GET['order_id'];
        //echo $order_primary_id; exit;
        $orderManagement = OrderManagement::select('payment_status', 'order_id', 'place_order_time')->where('id',$order_primary_id)->first();

        if($orderManagement->payment_status == 0 && isset($orderManagement->place_order_time)){

             $appid='mch37567';
             $privatekey = $this->private_key();
             set_time_limit(0);
             //$random_str = $this->generateRandomString();

             $class = new KsherPay($appid, $privatekey);
             $order_query_request_param = array(
                'mch_order_no'=>$orderManagement->id,
                'appid'=>$appid,
                //'nonce_str'=>$random_str,
                //'sign'=>$orderManagement->sign,
                //'time_stamp' =>Carbon::now()->format('Ymdhis')
            );

            $gateway_pay_response = $class->gateway_order_query($order_query_request_param);
            $gateway_pay_array = json_decode($gateway_pay_response, true);


        if(isset($gateway_pay_array['code']) && $gateway_pay_array['code'] == 0 && $gateway_pay_array['data']['result'] == 'SUCCESS'){

            $orderManagement->payment_status = 1;
            $orderManagement->payment_channel_from_ksher = $gateway_pay_array['data']['channel'];
            $orderManagement->order_status = 2;
            $orderManagement->payment_date  = $gateway_pay_array['data']['time_end'];
            $result = $orderManagement->save();

            if($result){
                $orderPayments = new Payment();
                $orderPayments->order_id = $order_primary_id;
                $orderPayments->payment_status = 1;
                $orderPayments->payment_method = $gateway_pay_array['data']['channel'];
                $orderPayments->payment_date  = $gateway_pay_array['data']['time_end'];
                $orderPayments->save();
             }
            }

            $orderManagement = OrderManagement::select('payment_status', 'order_id', 'place_order_time', 'order_status', 'payment_date')->where('id',$order_primary_id)->first();
                return json_encode($orderManagement);
        }
    }

    /**
     * @TODO Like I mentioned above, use built-in method for random string
     */
    function generateRandomString($length = 10) {
        return substr(str_shuffle(str_repeat($x='0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil($length/strlen($x)) )),1,$length);
    }

    public function getOrderPaymentDetails(Request $request){
        $paymentDetails = Payment::where('order_id', $request->order_id)->first();

        return response()->json([
            'amount' => $paymentDetails->amount,
            'payment_date' => date('d/m/Y', strtotime($paymentDetails->payment_date)),
            'payment_time' => $paymentDetails->payment_time,
            'payment_slip' => $paymentDetails->payment_slip,
            'payment_slip_url' => $paymentDetails->payment_slip_url,
        ]);
    }

    public function confirmPaymentForOrder(Request $request){
            $paymentDetails = Payment::where('order_id',$request->order_id)->first();
            $paymentDetails->is_confirmed = 1;
            $result = $paymentDetails->save();
            if($result){
                 $orders = OrderManagement::find($request->order_id);
                 $orders->payment_status = 1;
                 $orders->is_confirmed = 1;
                 $orders->order_status = OrderManagement::ORDER_STATUS_PROCESSING;
                 $orders->payment_channel_from_ksher = 'Bank Transfer';
                 $orders->payment_date  = Carbon::parse($request->payment_date)->format('Y-m-d h:i:s');
                 $orders->save();
            }
            return "ok";
    }

    public function makeNewPayment(Request $request){

            $paymentDetails = new PaymentManual();
            $paymentDetails->amount = $request->payment_amount;
            $paymentDetails->is_confirmed = $request->is_confirmed;
            $paymentDetails->order_id = $request->order_id;
            $paymentDetails->payment_date = Carbon::now()->format('Y-m-d');
            $paymentDetails->payment_time = Carbon::now()->format('h:i');
            $paymentDetails->payment_method = $request->payment_method;
            $paymentDetails->is_refund = $request->is_refund;
            $result = $paymentDetails->save();

            if($result){
                $orderManagementDtl = OrderManagement::select('in_total', 'payment_status')->where('id',$request->order_id)->first();
                $manualPaymentSumDtl = OrderManagement::getManualPaymentSum($request->order_id);
                $manualRefundedSumDtl = OrderManagement::getManualRefundedSum($request->order_id);
                $totalPaidAmount = $manualPaymentSumDtl - $manualRefundedSumDtl;

                if($orderManagementDtl->in_total == $totalPaidAmount){
                    $orders = OrderManagement::find($request->order_id);
                     $orders->payment_status = OrderManagement::PAYMENT_STATUS_PAID;
                     $orders->payment_channel_from_ksher = 'manual';
                     $orders->order_status = OrderManagement::ORDER_STATUS_PROCESSING;
                     $orders->save();
                }

                if($orderManagementDtl->in_total == $manualRefundedSumDtl){
                    $orders = OrderManagement::find($request->order_id);
                     $orders->payment_status = OrderManagement::PAYMENT_STATUS_UNPAID;
                     $orders->order_status = OrderManagement::ORDER_STATUS_PENDING;
                     $orders->save();
                }
             }

            $orderManagement = OrderManagement::select('in_total', 'payment_status')->where('id',$request->order_id)->first();
            $manualPaymentSum = OrderManagement::getManualPaymentSum($request->order_id);
            $manualRefundedSum = OrderManagement::getmanualRefundedSum($request->order_id);

            $paymentDetailsAllManual = PaymentManual::where('order_id',$request->order_id)->get();

            return view('seller.order_management.payment_table', compact('manualPaymentSum', 'paymentDetailsAllManual', 'orderManagement', 'manualRefundedSum'));
    }

    /**
     * @TODO Go through the statements, remove unused or commented codebase
     */
    public function updateManualPayment(Request $request){

            $paymentDetails = PaymentManual::find($request->payment_id);
            $paymentDetails->amount = $request->payment_amount;
            $paymentDetails->is_confirmed = $request->is_confirmed;
            $paymentDetails->payment_method = $request->payment_method;
            $paymentDetails->is_refund = $request->is_refund;
            $result = $paymentDetails->save();

             if($result){
                $orderManagementDtl = OrderManagement::select('in_total', 'payment_status')->where('id',$request->order_id)->first();
               $manualPaymentSumDtl = OrderManagement::getManualPaymentSum($request->order_id);
                $manualRefundedSumDtl = OrderManagement::getManualRefundedSum($request->order_id);
                $totalPaidAmount = $manualPaymentSumDtl - $manualRefundedSumDtl;

                if($orderManagementDtl->in_total == $totalPaidAmount){
                     $orders = OrderManagement::find($request->order_id);
                     $orders->payment_status = OrderManagement::PAYMENT_STATUS_PAID;
                     $orders->payment_channel_from_ksher = 'manual';
                     //$orders->payment_date  = Carbon::parse($request->payment_date)->format('Y-m-d h:i:s');
                     $orders->order_status = OrderManagement::ORDER_STATUS_PROCESSING;
                     $orders->save();
                }

                if($orderManagementDtl->in_total == $manualRefundedSumDtl){
                    $orders = OrderManagement::find($request->order_id);
                     $orders->payment_status = OrderManagement::PAYMENT_STATUS_UNPAID;
                     $orders->order_status = OrderManagement::ORDER_STATUS_PENDING;
                     $orders->save();
                }

            }

            $orderManagement = OrderManagement::select('in_total', 'payment_status')->where('id',$request->order_id)->first();
            $manualPaymentSum = OrderManagement::getManualPaymentSum($request->order_id);
            $manualRefundedSum = OrderManagement::getmanualRefundedSum($request->order_id);
            $paymentDetailsAllManual = PaymentManual::where('order_id',$request->order_id)->get();

            return view('seller.order_management.payment_table', compact('manualPaymentSum', 'paymentDetailsAllManual', 'orderManagement', 'manualRefundedSum'));
    }


    public function getManualPaymentData(Request $request){
        $paymentDetails = PaymentManual::where('id',$request->payment_id)->first();
        if($paymentDetails){
         $orderDetails = OrderManagement::where('id',$paymentDetails->order_id)->first();
        }

        return array(
            'paymentDetails'=>$paymentDetails,
            'orderDetails'=>$orderDetails
        );

    }

    public function delManualPaymentData(Request $request){
        $result = PaymentManual::where('id', $request->payment_id)->delete();

        if($result){
                $orderManagementDtl = OrderManagement::select('in_total', 'payment_status')->where('id',$request->order_id)->first();
                $manualPaymentSumDtl = OrderManagement::getManualPaymentSum($request->order_id);
                $manualRefundedSumDtl = OrderManagement::getManualRefundedSum($request->order_id);
                $totalPaidAmount = $manualPaymentSumDtl - $manualRefundedSumDtl;

                if($orderManagementDtl->in_total != $totalPaidAmount){
                     $orders = OrderManagement::find($request->order_id);
                     $orders->payment_status = OrderManagement::PAYMENT_STATUS_UNPAID;
                     $orders->order_status = OrderManagement::ORDER_STATUS_PENDING;
                     $orders->save();
                }

        }

        $orderManagement = OrderManagement::select('in_total', 'payment_status')->where('id',$request->order_id)->first();
        $manualPaymentSum = OrderManagement::getManualPaymentSum($request->order_id);
        $manualRefundedSum = OrderManagement::getmanualRefundedSum($request->order_id);
        $paymentDetailsAllManual = PaymentManual::where('order_id',$request->order_id)->get();

        return view('seller.order_management.payment_table', compact('manualPaymentSum', 'paymentDetailsAllManual', 'orderManagement', 'manualRefundedSum'));

    }

    public function bulkShipment(Request $request)
    {

        if ($request->ajax()) {

            if (isset($request->jSonData) && $request->jSonData != null) {
                $arr = json_decode($request->jSonData);
                foreach ($arr as $webIDOrderID) {

                    $orderManagement = OrderManagement::find($webIDOrderID);
                    $orderManagement->order_status = OrderManagement::ORDER_STATUS_PROCESSED;
                    $result = $orderManagement->save();

                    if($result){
                        $shipments = new Shipment();
                        $shipments->order_id = $webIDOrderID;
                        $shipments->shipment_date  = Carbon::parse($request->shipment_date)->format('Y-m-d');
                        $shipments->seller_id = Auth::user()->id;
                        $shipments->save();
                    }
                }

               echo "OK";
            }
        }
    }

    public function changeBankPaymentStatus(Request $request){
        try{
            if($request->payment_id){

            $paymentDetails = Payment::find($request->payment_id);
            $paymentDetails->is_confirmed = $request->current_status;
            $result = $paymentDetails->save();

            $payment_status = '';
            $order_status = '';
            if($request->current_status == Payment::PAYMENT_STATUS_YES){
                $payment_status = Payment::PAYMENT_STATUS_YES;
                $order_status = OrderManagement::ORDER_STATUS_PROCESSING;
            }

            if($request->current_status == Payment::PAYMENT_STATUS_NO){
                $payment_status = Payment::PAYMENT_STATUS_NO;
                $order_status = OrderManagement::ORDER_STATUS_PENDING;
            }

           if($result){
                $orderManagementDtl = OrderManagement::find($request->order_id);
                $orderManagementDtl->payment_status = $payment_status;
                $orderManagementDtl->order_status = $order_status;
                $orderManagementDtl->save();
           }

             $orderManagement = OrderManagement::select('in_total', 'payment_status')->where('id',$request->order_id)->first();
             $manualPaymentSum = PaymentManual::where('order_id',$request->order_id)->where('is_confirmed',1)->where('is_refund',0)->sum('amount');
             $manualRefundedSum = PaymentManual::where('order_id',$request->order_id)->where('is_confirmed',1)->where('is_refund',1)->sum('amount');
             $paymentDetailsAllManual = PaymentManual::where('order_id',$request->order_id)->get();
             $paymentDetailsOthers = Payment::where('order_id', $request->order_id)->first();

            return view('seller.order_management.payment_table', compact('manualPaymentSum', 'paymentDetailsAllManual', 'orderManagement', 'paymentDetailsOthers', 'manualRefundedSum'));
            }
        }
        catch (\Throwable $th) {
            report($th);

            DB::rollBack();

            return $this->apiResponse(Response::HTTP_INTERNAL_SERVER_ERROR, 'Sorry, something went wrong. ' . $th->getMessage());
        }

    }
    /**
     * @TODO Go through the statements, remove unused or commented codebase
     */
    public function getAllOrderedPro(Request $request){
        $allShipments = Shipment::where('order_id',$request->orderId)->where('seller_id',Auth::user()->id)->get();

        $editData = OrderManagement::where('id',$request->orderId)->with('orderProductDetails')->where('seller_id',Auth::user()->id)->first();

        $totalOrderedQty = OrderManagementDetail::where('order_management_id',$request->orderId)->where('seller_id',Auth::user()->id)->sum('quantity');

        $data = [];
        $product_price = [];

        // dd($editData->orderProductDetails);
        if(isset($editData->orderProductDetails) && count($editData->orderProductDetails)>0){


            foreach($editData->orderProductDetails as $key=>$row)
            {

                if(empty($row->discount_price) || $row->discount_price == NULL){
                    $product_price[$key]['price'] = $row->product->price;
                }
                else{
                    $product_price[$key]['price'] = $row->discount_price;
                }

            }
        }

        return view('seller.order_management.getAllOrderedPro', compact('editData', 'product_price', 'allShipments', 'totalOrderedQty'));
    }


    public function getAllOrderedProForOrder(Request $request){

        $editData = OrderManagement::where('id',$request->orderId)->with('orderProductDetails')->where('seller_id',Auth::user()->id)->first();

        $totalOrderedQty = OrderManagementDetail::where('order_management_id',$request->orderId)->where('seller_id',Auth::user()->id)->sum('quantity');

        $data = [];
        $product_price = [];

        if(isset($editData->orderProductDetails) && count($editData->orderProductDetails)>0){

            foreach($editData->orderProductDetails as $key=>$row)
            {

                if(empty($row->discount_price) || $row->discount_price == NULL){
                    $product_price[$key]['price'] = $row->product->price;
                }
                else{
                    $product_price[$key]['price'] = $row->discount_price;
                }

                $getShippedQty = ShipmentProduct::where('order_id', $row->order_management_id)
                ->where('product_id', $row->product_id)
                ->sum('quantity');

                if(!empty($getShippedQty)){
                    $product_price[$key]['shipped_qty'] = $getShippedQty;
                }
                else{
                    $product_price[$key]['shipped_qty'] = 0;
                }
             }
        }

        return view('seller.order_management.getAllOrderedProForOrder', compact('editData', 'product_price', 'totalOrderedQty'));
    }

    public function getAllOrderedProForOrderEdit(Request $request){

        $editData = OrderManagement::where('id',$request->orderId)->with('orderProductDetails')->where('seller_id',Auth::user()->id)->first();

        $totalOrderedQty = OrderManagementDetail::where('order_management_id',$request->orderId)->where('seller_id',Auth::user()->id)->sum('quantity');

        $shipmentDetails = Shipment::where('id',$request->shipment_id)->first();

        $data = [];
        $product_price = [];
        $shipmentQtyPerPro = [];

        if(isset($editData->orderProductDetails) && count($editData->orderProductDetails)>0){

            foreach($editData->orderProductDetails as $key=>$row)
            {

                if(empty($row->discount_price) || $row->discount_price == NULL){
                    $product_price[$key]['price'] = $row->product->price;
                }
                else{
                    $product_price[$key]['price'] = $row->discount_price;
                }

                $getShippedQty = ShipmentProduct::where('order_id', $row->order_management_id)
                ->where('product_id', $row->product_id)
                ->sum('quantity');

                if(!empty($getShippedQty)){
                    $product_price[$key]['shipped_qty'] = $getShippedQty;
                }
                else{
                    $product_price[$key]['shipped_qty'] = 0;
                }

                $shipmentQtyPerProduct = ShipmentProduct::where('order_id', $row->order_management_id)
                ->where('product_id', $row->product_id)
                ->where('shipment_id', $request->shipment_id)
                ->first();

                if(!empty($shipmentQtyPerProduct->quantity)){
                    $shipmentQtyPerPro[$key]['shipment_qty'] = $shipmentQtyPerProduct->quantity;
                }
                else{
                    $shipmentQtyPerPro[$key]['shipment_qty'] = 0;
                }
             }
        }

        return view('seller.order_management.getAllOrderedProForOrderEdit', compact('editData', 'product_price', 'totalOrderedQty', 'shipmentQtyPerPro', 'shipmentDetails'));
    }

    public function orderTakeAction(Request $request){
        try{
            if($request->action_value == '0'){
                $orderManagement = OrderManagement::where('id', $request->order_id)->first();
                $orderManagement->order_status = OrderManagement::ORDER_STATUS_CANCEL;
                $orderManagement->save();
            }

            if($request->action_value == '2' || $request->action_value == '4'){
                $orderManagement = OrderManagement::where('id', $request->order_id)->first();
                $orderManagement->order_status = OrderManagement::ORDER_STATUS_PROCESSING;
                $orderManagement->save();
            }

            if($request->action_value == 9){
                $orderManagement = OrderManagement::where('id', $request->order_id)->first();
                $orderManagement->order_status = OrderManagement::ORDER_STATUS_PENDING;
                $orderManagement->save();
            }
        }
        catch (\Throwable $th) {
            report($th);

            DB::rollBack();

            return $this->apiResponse(Response::HTTP_INTERNAL_SERVER_ERROR, 'Sorry, something went wrong. ' . $th->getMessage());
        }
         /**
         * @TODO use Exception instead of Throwable
         */
    }

    /**
     * @TODO Go through the statements, remove unused or commented codebase
     * There duplicate calls of OrderManagement
     * @TODO collect OrderManagement with common query and query on the collection
     */
    public function getShipmentDetailsData(Request $request){
        $order_id = $request->order_id;
        $getAllOredredDetails = OrderManagement::getAllOredredDetails($request->order_id);
        // $allShipments = DB::table('shipments')
        //             ->leftJoin('order_managements', 'order_managements.id', '=', 'shipments.order_id')
        //             ->leftJoin('customers', 'order_managements.customer_id', '=', 'customers.id')
        //             ->select('shipments.*', 'customers.customer_name')
        //             ->where('shipments.seller_id', '=', Auth::user()->id)
        //             ->where('shipments.is_custom', '=', 0)
        //             ->where('shipments.order_id', '=', $request->order_id)
        //             ->orderBy('id', 'DESC')
        //             ->get();

        // $order = OrderManagement::findOrFail($order_id);
        // foreach ($order->customer_shipping_methods as $customerShipping){
        //     if ($customerShipping->is_selected == CustomerShippingMethod::IS_SELECTED_YES){
        //         $shippingMethod = $customerShipping->shipping_cost->name;
        //     }
        // }

        return view('seller.order_management.shipmentDetails', compact('order_id', 'getAllOredredDetails'));
    }

    public function dataAllShipmentsOrders(Request $request)
    {
        $today = Carbon::today()->toDateString();
        $shipment_for = $request->shipment_for;

        if ($request->ajax()) {
            if (isset($request->status) && $request->status != null && $request->status != 'all') {
                $status = $request->status;
                $data = Shipment::getShipmentDataStatusWise($status, $shipment_for);

            }else{
               $data = Shipment::select('shipments.*', 'shops.name', 'shops.logo', 'customers.customer_name', 'channels.name as channnel_name', 'channels.image as channnel_image')
                    ->leftJoin('order_managements', 'order_managements.id', '=', 'shipments.order_id')
                    ->leftJoin('shops', 'order_managements.shop_id', '=', 'shops.id')
                    ->leftJoin('customers', 'order_managements.customer_id', '=', 'customers.id')
                    ->leftJoin('channels', 'order_managements.channel_id', '=', 'channels.id')
                    ->where('shipments.seller_id', '=', Auth::user()->id)
                    ->where('shipments.shipment_for', '=', $shipment_for)
                    ->get();

            }
            if (isset($request->shipment_status) && $request->shipment_status != null) {

               $data = Shipment::getShipmentDataShipStatusWise($request->shipment_status, $request->shipment_for);

            }
            if (isset($request->shipment_no) && $request->shipment_no != null) {
               $data = Shipment::getShipmentDataShipNoWise($request->shipment_no, $request->shipment_for);

            }

            // for order shipment details
            if (isset($request->order_id) && $request->order_id != null) {
               $data = Shipment::getShipmentDataForOrder($request->order_id, $request->is_custom);

            }

            //$data = OrderManagement::latest()->get();
            return Datatables::of($data)
                ->addIndexColumn()
                ->addColumn('checkbox', function ($row) {
                    return $row->id;
                })
                ->addColumn('details', function ($row) {
                    $ordersDetails = OrderManagementDetail::select('id')->where('order_management_id',$row->order_id)->get();
                    $getTotalItems = ShipmentProduct::where('shipment_products.shipment_id', '=', $row->id)
                    ->select('shipment_products.*')
                    ->get();

                    $shipment_status = '';
                    if($row->shipment_status == Shipment::SHIPMENT_STATUS_PENDING_STOCK){
                        $shipment_status = Shipment::getShipmentStatusStr(Shipment::SHIPMENT_STATUS_PENDING_STOCK);
                    }
                    if($row->shipment_status == Shipment::SHIPMENT_STATUS_READY_TO_SHIP){
                         $shipment_status = Shipment::getShipmentStatusStr(Shipment::SHIPMENT_STATUS_READY_TO_SHIP);
                    }
                    if($row->shipment_status == Shipment::SHIPMENT_STATUS_SHIPPED){
                         $shipment_status = Shipment::getShipmentStatusStr(Shipment::SHIPMENT_STATUS_SHIPPED);
                    }
                    if($row->shipment_status == Shipment::SHIPMENT_STATUS_CANCEL){
                         $shipment_status = Shipment::getShipmentStatusStr(Shipment::SHIPMENT_STATUS_CANCEL);
                    }

                    if($row->shipment_status == Shipment::SHIPMENT_STATUS_READY_TO_SHIP_PRINTED){
                         $shipment_status = Shipment::getShipmentStatusStr(Shipment::SHIPMENT_STATUS_READY_TO_SHIP_PRINTED);
                    }

                    if($row->shipment_status == Shipment::SHIPMENT_STATUS_WOO_HOLD){
                         $shipment_status = Shipment::getShipmentStatusStr(Shipment::SHIPMENT_STATUS_WOO_HOLD);
                    }

                    if($row->shipment_status == Shipment::SHIPMENT_STATUS_WOO_READY_TO_SHIP){
                         $shipment_status = Shipment::getShipmentStatusStr(Shipment::SHIPMENT_STATUS_WOO_READY_TO_SHIP);
                    }

                    if($row->shipment_status == Shipment::SHIPMENT_STATUS_WOO_PROCESSING){
                         $shipment_status = Shipment::getShipmentStatusStr(Shipment::SHIPMENT_STATUS_WOO_PROCESSING);
                    }

                    if($row->shipment_status == Shipment::SHIPMENT_STATUS_WOO_PENDING){
                         $shipment_status = Shipment::getShipmentStatusStr(Shipment::SHIPMENT_STATUS_WOO_PENDING);
                    }

                    if($row->shipment_status == Shipment::SHIPMENT_STATUS_WOO_COMPLETED){
                         $shipment_status = Shipment::getShipmentStatusStr(Shipment::SHIPMENT_STATUS_WOO_COMPLETED);
                    }

                    if($row->shipment_status == Shipment::SHIPMENT_STATUS_WOO_CANCEL){
                         $shipment_status = Shipment::getShipmentStatusStr(Shipment::SHIPMENT_STATUS_WOO_CANCEL);
                    }

                    $channelName = $row->channnel_name;
                    $shopName = '';
                    if(isset($row->name)){
                        $shopName = $row->name;
                    }
                    else{
                        $shopName = '';
                    }

                    if (!empty($row->channnel_name) && Storage::disk('s3')->exists($row->channnel_image) && !empty($row->channnel_image)) {
                        $image = Storage::disk('s3')->url($row->channnel_image);
                    }
                    else {
                        $image = Storage::disk('s3')->url('uploads/No-Image-Found.png');
                    }

                    $order = OrderManagement::findOrFail($row->order_id);
                    $shippingMethod = '';
                    foreach ($order->customer_shipping_methods as $customerShipping){
                        if ($customerShipping->is_selected == CustomerShippingMethod::IS_SELECTED_YES){
                            $shippingMethod = $customerShipping->shipping_cost->name;
                        }
                    }

                    $print_btn_action = '<button onclick="printLevel('.$row->id.','.$row->order_id.')" type="button" class="shipment_btns btn btn-sm btn-warning mb-1 mt-1 action_btns" id="" order-id="' . $row->order_id . '" data-id="' . $row->id . '"><i class="fa fa-print mr-1" aria-hidden="true"></i>'.__('translation.Print Label').'</button>';

                    $pack_btn_action = '<button onclick="packOrder('.$row->id.','.$row->order_id.')" type="button" class="shipment_btns mb-1 mt-1 btn btn-primary btn-sm action_btns" id="" order-id="' . $row->order_id . '" data-id="' . $row->id . '"><i class="fa fa-truck-pickup mr-1" aria-hidden="true"></i>'.__('translation.Pick Confirm').'</button>';

                    $pack_btn_action_cancel = '<button onclick="pickOrderCancel('.$row->id.','.$row->order_id.')" type="button" class="shipment_btns mb-1 mt-1 btn btn-danger btn-sm action_btns" id="" order-id="' . $row->order_id . '" data-id="' . $row->id . '"><i class="fa fa-trash mr-1" aria-hidden="true"></i>'.__('translation.Cancel').'</button>';

                    $mark_as_shipped_btn_action = '<button onclick="markAsShipped('.$row->id.','.$row->order_id.')" type="button" class="shipment_btns mb-1 mt-1 btn btn-success btn-sm action_btns" id="" order-id="' . $row->order_id . '" data-id="' . $row->id . '"><i class="fa fa-shipping-fast mr-1" aria-hidden="true"></i>'.__('translation.Mark as Shipped').'</button>';

                    $mark_as_shipped_btn_action_update = '<button onclick="markAsShippedUpdate('.$row->id.','.$row->order_id.')" type="button" class="shipment_btns mb-1 mt-1 btn btn-info btn-sm action_btns" id="" order-id="' . $row->order_id . '" data-id="' . $row->id . '"><i class="fa fa-shipping-fast mr-1" aria-hidden="true"></i>'.__('translation.Update').'</button>';


                    if($row->print_status == 1){
                    $print_time = Carbon::parse($row->print_date_time)->format('d/m/Y h:i a');
                    $userDetails = User::select('name')->where('id',$row->print_by)->first();

                    $print_by_btn =  '<div class="action_butn_style"><button disabled class="shipment_btns  btn-sm  mb-1 mt-1 action_btns btn_status_after">Printed On :<strong><br>'.$print_time.'<br></strong> (<font class="text-blue-500">'.$userDetails->name.'</font>)</button>
                        <br>'.$print_btn_action.'</div>
                        ';
                    }
                    else{
                     $print_by_btn =  $print_btn_action;
                    }

                    if($row->pack_status == 1){

                        $packed_date_time = Carbon::parse($row->packed_date_time)->format('d/m/Y h:i a');
                        $userDetails = User::select('name')->where('id',$row->packed_by)->first();


                        $pack_status_btn =  '<div class="action_butn_style"><button disabled class="shipment_btns  btn-sm  mb-1 mt-1 action_btns btn_status_after">Pick Confirm :<strong><br>'.$packed_date_time.'<br></strong> (<font class="text-blue-500">'.$userDetails->name.'</font>)</button>
                            <br>'.$pack_btn_action_cancel.'</div>
                            ';
                    }
                    else{
                        $pack_status_btn = $pack_btn_action;
                    }

                    if($row->mark_as_shipped_status == 1){

                        $mark_as_shipped_date_time = Carbon::parse($row->mark_as_shipped_date_time)->format('d/m/Y h:i a');
                        $userDetails = User::select('name')->where('id',$row->mark_as_shipped_by)->first();


                        $mark_as_shipped_btn =  '<div class="action_butn_style"><button disabled class="shipment_btns  btn-sm  mb-1 mt-1 action_btns btn_status_after">Mark Shipped On :<strong><br>'.$mark_as_shipped_date_time.'<br></strong> (<font class="text-blue-500">'.$userDetails->name.'</font>)</button>
                            <br>
                            '.$mark_as_shipped_btn_action_update.'</div>
                            ';
                    }
                    else{
                        $mark_as_shipped_btn = $mark_as_shipped_btn_action;
                    }

                    if($row->is_custom == 0){
                        $editButtonAction = 'edit_new_shipment';
                        $deleteButtonAction = 'delete_new_shipment';
                    }
                    else{
                       $editButtonAction = 'edit_new_shipment_custom';
                       $deleteButtonAction = 'delete_new_shipment_custom';
                    }

                   return '<div class="border-div col-lg-12">
                    <div class="row border-bottom-dotted common_padding_5">
                            <div class="col-lg-3 text-center"> Ship ID: #<strong>'.$row->id.'</strong> </div>
                            <div class="col-lg-6 text-center">'.$shipment_status.'</div>
                            <div class="col-lg-3 text-left">
                                <div class="margin_top_mb_10">
                            Shipment Date : <strong>'.Carbon::parse($row->shipment_date)->format('d/m/Y').'</strong>
                            </div>
                            </div>
                        </div>
                        <div class="row common_padding_5">
                            <div class="col-lg-3 text-center">
                              <div class="mb-1 md:mb-3">
                                <img src="'. $image .'" alt="'. $channelName .'" title="'. $channelName .'" class="w-10 h-auto" />
                            </div>
                            <div class="">
                                <span class="badge-status--yellow">
                                    '. $shopName .'
                                </span>
                            </div>
                            </div>
                            <div class="col-lg-9 text-left">
                            <div class="row">
                            <div class="col-lg-12">
                                <div class="width-90 float-left">
                                <font>Customer Name : <strong>'.$row->customer_name.'</strong></font><br>
                                <font class="mb-2 margin_bottom_10">Total Items: <strong class="text-underline cursor" onclick="see_total_items('.$row->id.','.$row->order_id.')">'.count($getTotalItems).'</strong></font><br>
                                <div class="mb-3">
                                    <span class="text-gray-600">
                                        Shipped Method:
                                    </span>
                                    <a data-id="'. $row->id .'" id="BtnAddress" class="modal-open cursor-pointer">' . $shippingMethod .'</a>
                                </div>
                                </div>
                                <div class="width-10 float-left">
                                <button type="button" data-id="'. $row->id .'" id="'.$editButtonAction.'" class="modal-open btn-action--yellow">
                                 <i class="fas fa-pencil-alt"></i>
                                </button><br>
                                <button type="button" data-id="'. $row->id .'" class="btn btn-danger btn-sm" id="'.$deleteButtonAction.'">
                                <i class="fas fa-trash"></i>
                                </button>
                                </div>
                            </div>
                            </div>
                            <div class="col-lg-12">
                                <div class="row">
                                <div class="shipment_action_btn">
                                 '.$print_by_btn.'<br>

                                </div>
                                <div class="shipment_action_btn">
                                '.$pack_status_btn.'<br>

                                </div>
                                <div class="shipment_action_btn">
                                 '.$mark_as_shipped_btn.'<br>

                                </div>
                            </div>
                            </div>
                            </div>

                        </div>
                        </div>';
                })
                ->addColumn('id', function ($row) {
                    $ordersDetails = OrderManagementDetail::select('id')->where('order_management_id',$row->order_id)->get();
                    return 'Shipment ID : <strong>#'.$row->id.'</strong><br>
                    Shipment Date : '.Carbon::parse($row->shipment_date)->format('d-M-Y h:i:a').'<br>Order ID : <strong>#'.$row->order_id.'</strong><br>Total Item : <strong>'.count($ordersDetails).'</strong>';
                })

                 ->addColumn('print_level', function ($row) {

                    if($row->print_status == 1){
                        $print_time = Carbon::parse($row->print_date_time)->format('d-M-Y h:i a');
                        $userDetails = User::select('name')->where('id',$row->print_by)->first();
                        return '<strong>Printed by</strong> : <br>'.$userDetails->name.'<br><strong>'.$print_time.'</strong>';
                    }
                    else{
                     return '<button type="button" class="btn btn-success btn-sm" id="printLevel" order-id="' . $row->order_id . '" data-id="' . $row->id . '">'.__('translation.Print Label').'</button>';
                    }

                 })
                 ->addColumn('pack_order', function ($row) {
                    if($row->pack_status == 0){
                    return '<button type="button" class="btn btn-warning btn-sm" id="packOrder" order-id="' . $row->order_id . '" data-id="' . $row->id . '">Pack</button>';
                    }
                    if($row->pack_status == 1){
                        $packed_time = Carbon::parse($row->packed_date_time)->format('d-M-Y h:i a');
                        $userDetails = User::select('name')->where('id',$row->packed_by)->first();
                        return '<strong>Packed by</strong> : <br> '.$userDetails->name.'<br><strong>'.$packed_time.'</strong>';
                    }
                 })


                 ->addColumn('shipment_status', function ($row) {
                    if($row->shipment_status == 10){
                        return '<button type="button" class="btn btn-danger btn-sm">'.__('translation.Wanting for Stock').'</button>';
                    }
                    if($row->shipment_status == 11){
                        return '<button type="button" class="btn btn-danger btn-sm"> '. __('translation.Ready To ship') .'</button>';
                    }
                    if($row->shipment_status == 12){
                        return '<button type="button" class="btn btn-danger btn-sm"> '.__('translation.Shipped').'</button>';
                    }
                    if($row->shipment_status == 13){
                        return '<button type="button" class="btn btn-danger btn-sm">'. __('translation.Cancelled').'</button>';
                    }
               })
                 ->rawColumns(['checkbox','details','id', 'shipment_status', 'print_level', 'pack_order'])
                ->make(true);
        }
    }

    public function getCustomShipmentDetailsData(Request $request){
        $order_id = $request->order_id;
        $allShipments = Shipment::where('shipments.seller_id', '=', Auth::user()->id)
                     ->where('shipments.order_id', '=', $request->order_id)
                     ->where('shipments.is_custom', '=', 1)
                     ->leftJoin('order_managements', 'order_managements.id', '=', 'shipments.order_id')
                    ->leftJoin('customers', 'order_managements.customer_id', '=', 'customers.id')
                    ->select('shipments.*', 'customers.customer_name')
                    ->get();

        return view('seller.order_management.customShipmentDetails', compact('order_id', 'allShipments'));

    }

    public function getModalContentForCustomShipment(Request $request){
        $order_id = $request->orderId;
        $editData = OrderManagement::where('id',$request->orderId)->with('orderProductDetails')->where('seller_id',Auth::user()->id)->first();
        return view('seller.order_management.getModalContentForCustomShipment', compact('order_id', 'editData'));
    }

    public function getModalContentForEditCustomShipment(Request $request){
        $order_id = $request->orderId;
        $use_for = $request->use_for;
        $editData = OrderManagement::where('id',$request->orderId)->with('orderProductDetails')->where('seller_id',Auth::user()->id)->first();
        $shipmentDetails = Shipment::where('id',$request->shipment_id)->first();
        $getShipmentsProductsDetails = ShipmentProduct::where('shipment_id',$request->shipment_id)
                                     ->leftJoin('products', 'shipment_products.product_id', '=', 'products.id')
                                     ->select('products.*', 'shipment_products.quantity')
                                     ->get();

        if($use_for == 'edit'){
         return view('seller.order_management.getModalContentForEditCustomShipment', compact('order_id', 'editData', 'shipmentDetails', 'getShipmentsProductsDetails'));
        }

        if($use_for == 'view'){
        return view('seller.order_management.getModalContentForViewCustomShipment', compact('order_id', 'editData', 'shipmentDetails', 'getShipmentsProductsDetails'));
        }


    }

    public function getOrderedProductDetails(Request $request){
        $order_id = $request->orderId;
        $product_id = $request->product_id;
        $editData = Product::where('id',$request->product_id)->where('seller_id',Auth::user()->id)->first();

        $shipped_qty = '';
        $product_price = '';
        if(isset($editData)){

            $getShippedQty = ShipmentProduct::where('order_id', $request->orderId)
            ->where('product_id', $request->product_id)
            ->sum('quantity');

            if(!empty($getShippedQty)){
                $shipped_qty = $getShippedQty;
            }
            else{
                $shipped_qty = 0;
            }

        }

        return view('seller.order_management.orderedProductedDetails', compact('order_id', 'editData', 'shipped_qty', 'product_price'));
    }


    /**
     * @TODO Go through the statements, remove unused or commented codebase
     */
    public function allShipmentData(Request $request)
    {
          if ($request->ajax()) {
            if (isset($request->order_id) && $request->order_id != null) {
                $order_id = $request->order_id;
                $data = DB::table('shipments')
                    ->leftJoin('order_managements', 'order_managements.order_id', '=', 'shipments.order_id')
                    ->select('shipments.*')
                    ->where('shipments.seller_id', '=', Auth::user()->id)
                    ->where('shipments.order_id', '=', $request->order_id)
                    ->get();

            }

            return Datatables::of($data)
                ->addIndexColumn()
                ->addColumn('checkbox', function ($row) {
                    return $row->id;
                })
                ->addColumn('shipment_data', function ($row) {
                    $ordersDetails = OrderManagementDetail::select('id')->where('order_management_id',$row->order_id)->get();
                })
                ->rawColumns(['checkbox','shipment_data'])
                ->make(true);
        }
    }

    public function deleteShipmentForOrder(Request $request){
        $shipment = Shipment::where('id', $request->shipment_id)->where('seller_id', Auth::user()->id)->first();

        if($shipment) {
            $order = OrderManagement::where('id', $shipment->order_id)->first();
            $order->order_status = OrderManagement::ORDER_STATUS_PROCESSING;
            $order->update();

            $result = $shipment->delete();
            if ($result) {
                ShipmentProduct::where('shipment_id', $request->shipment_id)->delete();
            }
        }
    }

    public function deleteCustomShipmentForOrder(Request $request){
        $result = Shipment::where('id',$request->shipment_id)->where('seller_id',Auth::user()->id)->delete();
        if($result){
             ShipmentProduct::where('shipment_id',$request->shipment_id)->delete();
        }

    }
     public function markAsShipped(Request $request){
       $shipments = Shipment::find($request->shipment_id);
       $shipments->shipment_status = Shipment::SHIPMENT_STATUS_SHIPPED;
       $shipments->mark_as_shipped_status = 1;
       $shipments->mark_as_shipped_date_time = Carbon::now()->format('Y-m-d h:i:s');
       $shipments->mark_as_shipped_by = Auth::user()->id;
       $result = $shipments->save();
    }

    public function updateOrderStatusByUser(Request $request)
    {
        try {
            $orderId = $request->id;

            $orderDetails = OrderManagement::where('id', $orderId)->first();
            $orderDetails->order_status = $request->orderStatus;
            $orderDetails->update();

            return $this->apiResponse(Response::HTTP_OK, 'Order Status successfully updated.');

        } catch (\Throwable $th) {
            report($th);

            return $this->apiResponse(Response::HTTP_INTERNAL_SERVER_ERROR, 'Sorry, something went wrong. ' . $th->getMessage());
        }
    }

    public function getCustomerAddressForOrder(Request $request)
    {
        if ($request->ajax()) {
            if (isset($request->id) && $request->id != null) {
                $platform = "shopee";
                $id = $request->id;

                $data = ShopeeOrderPurchase::where('order_id', $request->id)->first();
                if(isset($data->shipping)){
                    $shipping = json_decode($data->shipping);


                    $shipping_name  = isset($shipping->name)?$shipping->name ." ":"";
                    $shipping_phone = isset($shipping->phone)?$shipping->phone:"";
                    $shipping_country = isset($shipping->country)?$shipping->country:"";
                    $shipping_address_1 = isset($shipping->full_address)?$shipping->full_address:"";
                    $shipping_city = isset($shipping->city)?$shipping->city:"";
                    $shipping_state = isset($shipping->state)?$shipping->state:"";
                    $shipping_district = isset($shipping->district)?$shipping->district:"";
                    $shipping_postcode = isset($shipping->zipcode)?$shipping->zipcode:"";

                    return  array(
                        'shipping_name' => $shipping_name,
                        'shipping_phone' => $shipping_phone,
                        'shipping_address_1' => $shipping_address_1,
                        'shipping_city' => $shipping_city,
                        'shipping_state' => $shipping_state,
                        'shipping_district' => $shipping_district,
                        'shipping_postcode' => $shipping_postcode
                    );
                }
            }
        }
    }

}
