<?php

namespace App\Http\Controllers;

use App\Enums\DueStatusEnum;
use App\Enums\OrderPurchaseStatusEnum;
use App\Http\Requests\DatatableRequest;
use App\Http\Requests\OrderPurchase\StoreRequest;
use App\Http\Requests\OrderPurchase\UpdateRequest;
use App\Http\Resources\ProductTypeAheadResource;
use App\Models\ExchangeRate;
use App\Models\OrderPurchase;
use App\Models\OrderPurchaseDetail;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\ShipType;
use App\Models\DomesticShipper;
use App\Models\PoShipment;
use App\Models\PoShipmentDetail;
use App\Models\AgentCargoName;
use App\Models\AgentCargoMark;
use App\Models\PoPayments;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Datatables;
use PDF;

class OrderPurchaseController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $suppliers = Supplier::getDefaultSuppliers();

        $orderPurchaseTotalByGroup = OrderPurchase::orderPurchaseTotalByGroup();

        $orderPurchaseTotalAll = array();
        //Re-Order array and set total to each status
        if(!empty($orderPurchaseTotalByGroup)){
            foreach($orderPurchaseTotalByGroup as $item){
                $PO[$item->status] = $item->total;
                $orderPurchaseTotalAll[]=$item->total;
            }
        }

        $data = [
            'orderPurchaseTotalAll' => array_sum($orderPurchaseTotalAll),
            'orderPurchaseTotalOpen' => isset($PO['open']) ? $PO['open'] : 0,
            'orderPurchaseTotalArrive' => isset($PO['arrive']) ? $PO['arrive'] : 0,
            'orderPurchaseTotalClose' => isset($PO['close']) ? $PO['close'] : 0,
            'suppliers' => $suppliers,
            'dueStatus' => DueStatusEnum::toArray()
        ];

        return view('seller.purchase_order.index', $data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        Session::put('itemArray', []);

        $sellerId = Auth::user()->id;

        $products = Product::getProductsBySellerID($sellerId);
        $suppliers = Supplier::getSuppliersBySellerID($sellerId);
        if(Auth::user()->role == 'staff'):
            $products = Product::all();
            $suppliers = Supplier::all();
        endif;
        //Generate a timestamp using mt_rand.
        $timestamp = mt_rand(1, time());

        $data = [
            'timestamp' => $timestamp,
            'products' => ProductTypeAheadResource::collection($products),
            'shiptypes' => ShipType::all(),
            'cargos' => AgentCargoName::all(),
            'domesticShippers' =>  DomesticShipper::all(),
            'suppliers' =>  $suppliers,
            'exchangeRates' => ExchangeRate::all()
        ];

        return view('seller.purchase_order.create', $data);
    }

    /**
     * Store `order_purchase` data
     *
     * @param  \App\Http\Requests\OrderPurchase\StoreRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreRequest $request)
    {


        try {
            $orderPurchase = new OrderPurchase();
            $orderPurchase->supplier_id = $request->supplier_id;
            $orderPurchase->seller_id = Auth::user()->id;

            // Order Date
            $order_date = $request->order_date ? date('Y-m-d', strtotime($request->order_date)) : NULL ;

            // Estimate Arival Date From
           // $e_a_d_f = $request->e_a_d_f ? date('Y-m-d', strtotime($request->e_a_d_f)) : NULL ;

            // Estimate Arival Date To
           // $e_a_d_t = $request->e_a_d_t ? date('Y-m-d', strtotime($request->e_a_d_t)) : NULL ;

            // Ship Date
            //$ship_date = $request->ship_date ? date('Y-m-d', strtotime($request->ship_date)) : NULL ;

            $orderPurchase->supply_from = $request->check;
            if (!isset($request->check)) {
                $orderPurchase->supply_from = 0;
            }

            $orderPurchase->status = 'open';
            //$orderPurchase->note = $request->note;
            //$orderPurchase->factory_tracking = $request->factory_tracking ? $request->factory_tracking: '-';
            $orderPurchase->shipping_type_id = $request->shipping_type_id ? $request->shipping_type_id : '';
            $orderPurchase->agent_cargo_id = $request->agent_cargo_id;
            $orderPurchase->shipping_mark_id = $request->shipping_mark_id;
            //$orderPurchase->domestic_shipper_id = $request->domestic_shipper_id;
           // $orderPurchase->cargo_ref = $request->cargo_ref;
            $orderPurchase->order_date = $order_date;
            //$orderPurchase->ship_date = $ship_date;
            //$orderPurchase->number_of_cartons = $request->number_of_cartons ? $request->number_of_cartons  : '0';
            //$orderPurchase->domestic_logistics = $request->domestic_logistics ? $request->domestic_logistics : '';
            //$orderPurchase->number_of_cartons1 = $request->number_of_cartons1 ? $request->number_of_cartons1 : '';
            //$orderPurchase->domestic_logistics1 = $request->domestic_logistics1 ? $request->domestic_logistics1 : '';
            $orderPurchase->save();

            /** Save Order Purchase Details  */
            $this->saveOrderPurchaseDetails($request,$orderPurchase->id);


             /** Save PO Shipment & Shipment Details Info  */
             PoShipment::updatePoShipmentPurchaseID($request->order_purchase_id,$orderPurchase->id);
             PoShipmentDetail::updatePoShipmentDetailsPurchaseID($request->order_purchase_id,$orderPurchase->id);

             //$this->POShipmentAndShipmentDetails($request,$orderPurchase->order_purchase_id,$orderPurchase->id);

            /** Save Payment Info  */
            $this->savePaymentDetails($request,$orderPurchase->id);


            return response()->json([
                'message' => 'Order Purchase created successfully'
            ]);

        } catch (\Exception $th) {
            report($th);

            return response()->json([
                'message' => 'Something went wrong. ' . $th->getMessage()
            ], 500);
        }
    }





    /**
     * Update the `order_purchase` data
     *
     * @param  \App\Http\Requests\OrderPurchase\UpdateRequest $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateRequest $request, $id)
    {

        try {


            $orderPurchase = OrderPurchase::find($id);
            $sellerId = Auth::user()->id;
            $orderPurchase->supplier_id = $request->supplier_id;

            // Order Date
            $order_date = $request->order_date ? date('Y-m-d', strtotime($request->order_date)) : NULL ;


            $orderPurchase->supply_from = $request->check;
            if (!isset($request->check)) {
                $orderPurchase->supply_from = 0;
            }

            //$orderPurchase->note = $request->note;
            //$orderPurchase->factory_tracking = $request->factory_tracking ? $request->factory_tracking: '-';
            $orderPurchase->shipping_type_id = $request->shipping_type_id ? $request->shipping_type_id : '';
            $orderPurchase->agent_cargo_id = $request->agent_cargo_id;
            $orderPurchase->shipping_mark_id = $request->shipping_mark_id;
            //$orderPurchase->domestic_shipper_id = $request->domestic_shipper_id;
           // $orderPurchase->cargo_ref = $request->cargo_ref;
           $orderPurchase->order_date = $order_date;
           //$orderPurchase->ship_date = $ship_date;
           //$orderPurchase->number_of_cartons = $request->number_of_cartons ? $request->number_of_cartons  : '0';
           //$orderPurchase->domestic_logistics = $request->domestic_logistics ? $request->domestic_logistics : '';
           //$orderPurchase->number_of_cartons1 = $request->number_of_cartons1 ? $request->number_of_cartons1 : '';
           //$orderPurchase->domestic_logistics1 = $request->domestic_logistics1 ? $request->domestic_logistics1 : '';

            $orderPurchase->save();

            /** Delete Existing Order Purchase Details  */
            OrderPurchaseDetail::where('order_purchase_id', $orderPurchase->id)->where('seller_id', $sellerId)->delete();
            /** Save Order Purchase Details  */
            $this->saveOrderPurchaseDetails($request,$orderPurchase->id);

            /** Save PO Shipment & Shipment Details Info  */
            PoShipment::updatePoShipmentPurchaseID($request->order_purchase_id,$orderPurchase->id);
            PoShipmentDetail::updatePoShipmentDetailsPurchaseID($request->order_purchase_id,$orderPurchase->id);


            /** Save Payment Info  */
           $this->savePaymentDetails($request,$orderPurchase->id);

            return response()->json([
                'message' => 'Order Purchase Updated Successfully'
            ]);

        } catch (\Exception $th) {
            report($th);

            return response()->json([
                'message' => 'Something happened wrong ' . $th->getMessage()
            ], 500);
        }
    }





     /**
     * This function Save Order Purchase Details
     *
     * @param request  mixed data, int po id
     */
    private function saveOrderPurchaseDetails($request,$order_purchase_id){

        if (!empty($request->product_id)) {

            foreach($request->product_id as $key => $product_id ) {
                $exchangeRateInput = $request->exchange_rate_id[$key] ?? 0;

                $exchangeRateId = 0;
                $exchangeRateValue = 0;
                if ($exchangeRateInput > 0) {
                    $exchangeRate = ExchangeRate::where('id', $exchangeRateInput)->first();
                    $exchangeRateId = $exchangeRate->id ?? 0;
                    $exchangeRateValue = $exchangeRate->rate ?? 0;
                }

                $orderPurchaseDetails = new OrderPurchaseDetail();
                $orderPurchaseDetails->order_purchase_id = $order_purchase_id;
                $orderPurchaseDetails->product_id = $product_id;
                $orderPurchaseDetails->product_price = $request->product_price[$key];
                $orderPurchaseDetails->quantity = $request->product_quantity[$key];
                $orderPurchaseDetails->seller_id = Auth::user()->id;
                $orderPurchaseDetails->po_status = 'open';
                $orderPurchaseDetails->exchange_rate_id = $exchangeRateId;
                $orderPurchaseDetails->exchange_rate_value = $exchangeRateValue;

                $orderPurchaseDetails->save();
            }
        }
    }

     /**
     * This function Save Payment Details
     *
     * @param request  mixed data, int po id
     */
    private function savePaymentDetails($request,$order_purchase_id){

        $file_invoice = NULL;
        $file_payment = NULL;
        $supplier_id = isset($request->supplier_id) ? $request->supplier_id : 0;
        $payment_id = isset($request->payment_id) ? $request->payment_id : 0;
        //TO Save Uploaded invoice file file into folder
        if ($request->hasFile('file_invoice')) {
            $upload = $request->file('file_invoice');
            $file_type = $upload->getClientOriginalExtension();
            $upload_name_file_invoice =  time() . $upload->getClientOriginalName();
            $destinationPath = public_path('uploads/payment');
            $upload->move($destinationPath, $upload_name_file_invoice);
            $file_invoice = 'uploads/payment/'.$upload_name_file_invoice;
        }
        //TO Save Uploaded payment file into folder
        if ($request->hasFile('file_payment')) {
            $upload = $request->file('file_payment');
            $file_type = $upload->getClientOriginalExtension();
            $upload_file_payment_name =  time() . $upload->getClientOriginalName();
            $destinationPath = public_path('uploads/payment');
            $upload->move($destinationPath, $upload_file_payment_name);
            $file_payment = 'uploads/payment/'.$upload_file_payment_name;
        }

        $data = array(
            'order_purchase_id' => $order_purchase_id,
            'supplier_id' => $request->supplier_id ?? 0,
            'exchange_rate_id' => $request->payment_exchange_rate_id ?? 0,
            'amount' => $request->amount ?? 0,
            'paid' => $request->paid ?? 0,
            'bank_account' => $request->bank_account ?? '',
            'notes' => $request->notes ?? '',
            'payment_status' => $request->payment_status ?? '',
            'file_invoice' => $file_invoice ?? '',
            'file_payment' => $file_payment ?? '',
            "user_id" => Auth::user()->id,
        );

        if($payment_id>0){
            $data["updated_at"] = date('Y-m-d');
            $insertedId = OrderPurchase::updatePayment('po_payments',$payment_id,$data);
        }else{
            $data["created_at"] = date('Y-m-d');
            $insertedId = OrderPurchase::insertTableData('po_payments', $data);
        }
    }

    /**
     * Show the order_purchase detail
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {

        $sellerId = Auth::user()->id;

        $orderPurchase = OrderPurchase::getAllPOData($id,$sellerId);

        $payment  = OrderPurchase::getAllPaymentInfo($id,$sellerId);
        abort_if(!$orderPurchase, 404, __('translation.Data not found'));

        $data = [
            'orderPurchase' => $orderPurchase,
            'payment' => $payment,
            'supplyFroms' => OrderPurchase::getAllSupplyFrom(),
            'supplyFromImport' => OrderPurchase::SUPPLY_FROM_IMPORT
        ];

        return view('seller.purchase_order.show', $data);
    }


     // Generate PDF
     public function pdfview(Request $request)
    {
        $sellerId = Auth::user()->id;

        $orderPurchase = OrderPurchase::getAllPOData($request->id,$sellerId);

        abort_if(!$orderPurchase, 404, __('translation.Data not found'));

        $data = [
            'orderPurchase' => $orderPurchase,
            'shiptypes' => ShipType::all(),
            'cargos' => AgentCargoName::all(),
            'shipping_marks' => AgentCargoMark::all(),
            'supplyFroms' => OrderPurchase::getAllSupplyFrom(),
            'supplyFromImport' => OrderPurchase::SUPPLY_FROM_IMPORT
        ];

        set_time_limit(300);
        $pdf = PDF::loadView('seller.purchase_order.po_sheet',$data);
        return $pdf->download('seller.purchase_order.po_sheet',$data);
        //return view('seller.purchase_order.show');
    }



    public function getProductWisePOShippingInfo(Request $request)
    {
        $order_purchase_id = $request->order_purchase_id ? $request->order_purchase_id : 0;

        if (!empty($request->productCodes)) {

            $data = Product::getProductDetailsWithIncommingAndShipped($request->productCodes,$order_purchase_id, Auth::user()->id);
            $distinct_po_shipment_details = PoShipmentDetail:: where('order_purchase_id', $order_purchase_id)->get();

            abort_if(!$data, 404, 'Product not found');
            // dd($data);

            return response()->json([
                'message' => 'success',
                'data' => $data[0],
                'distinct_po_shipment_details' => $distinct_po_shipment_details,
            ]);
        }

    }





    public function getTotalShippedQtyByPOIDAndProductID(Request $request)
    {
        $order_purchase_id = $request->order_purchase_id ? $request->order_purchase_id : 0;

        if (!empty($request->order_purchase_id)) {

            $po_shipment_details = Product::getProductDetailsWithIncommingAndShipped($request->productCode,$order_purchase_id, Auth::user()->id);

            /*if(!empty($po_shipment_details)){
                foreach($po_shipment_details as $details){
                    $arr[$details->product_id]= $details->ship_quantity;
                }

            }
            */
            //abort_if(!$po_shipment_details, 404, 'Product not found');
            // dd($data);

            return response()->json([
                'message' => 'success',
                'po_shipment_details' => $po_shipment_details[0],
            ]);
        }

    }


    /**
     * Show the form for editing the specified resource.
     *
     * @param  mixed
     * @return \Illuminate\Http\Response
     */
    public function addPoShipment(Request $request)
    {

        if ($request->order_purchase_id) {

            $order_purchase_id = $request->order_purchase_id;
            //Delete If Exist
            if($request->id > 0){
                PoShipment::deleteByID($request->id);
                PoShipmentDetail::deleteByPoShipmentID($request->id);
            }


            $data_ship = array(
                // order_purchase_id not exist or new then it will set random id as order purchase ID
                'order_purchase_id' => $order_purchase_id,
                // These 5 rows data will come from 1st Tab
                'supplier_id' => $request->supplier_id,
                'order_date' => $request->order_date ? date('Y-m-d', strtotime($request->order_date)) :  NULL,
                'shipping_type_id' => $request->shipping_type_id ? $request->shipping_type_id: '-',
                'shipping_mark_id' => $request->shipping_mark_id ? $request->shipping_mark_id: '-',
                'agent_cargo_id' => $request->agent_cargo_id ? $request->agent_cargo_id : '-',


                'factory_tracking' => $request->factory_tracking ? $request->factory_tracking : '-',
                'domestic_shipper_id' => $request->domestic_shipper_id ? $request->domestic_shipper_id : '-',
                'cargo_ref' => $request->cargo_ref? $request->cargo_ref: '-',

                'e_a_d_f' => isset($request->e_a_d_f) ? date('Y-m-d', strtotime($request->e_a_d_f)) : '' ,
                'e_a_d_t' => isset($request->e_a_d_t) ? date('Y-m-d', strtotime($request->e_a_d_t)) : '',
                'ship_date' => isset($request->ship_date) ? date('Y-m-d', strtotime($request->ship_date)) : '',
                'status' => $request->status,
                'number_of_cartons' => $request->number_of_cartons ? $request->number_of_cartons : '-',
                'domestic_logistics' => $request->domestic_logistics ? $request->domestic_logistics : '-',

                "created_at" =>date('Y-m-d'),
                "seller_id" => Auth::user()->id,

            );



            $new_po_shipment_id = OrderPurchase::insertTableData('po_shipments', $data_ship);
            //return print_r($request->arr_po_shipment_details);

            if(!empty($request->arr_po_shipment_details)){

                foreach($request->arr_po_shipment_details as $key => $details ) {
                    $data_ship_details = array(
                        'po_shipment_id' => $new_po_shipment_id,
                        'order_purchase_id' => $order_purchase_id,
                        'product_id' =>  $details['product_id'],
                        'ship_quantity' => $details['ship_quantity'] ?? 0,
                        "created_at" => date('Y-m-d'),
                        "seller_id" => Auth::user()->id,
                    );

                OrderPurchase::insertTableData('po_shipment_details', $data_ship_details);
                }
            }



            //$data = Product::getProductDetailsWithIncommingAndShipped($request->productCodes,$order_purchase_id, Auth::user()->id);
            //$distinct_po_shipment_details = PoShipmentDetail:: where('order_purchase_id', $order_purchase_id)->get();

          //  abort_if(!$data, 404, 'Product not found');
            // dd($data);

            return response()->json([
                'message' => 'success',
                'po_shipments' =>  OrderPurchase::poShipmentDetailsByOrderPurchaseID($order_purchase_id),

            ]);
        }

    }



    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $sellerId = Auth::user()->id;

        $orderPurchase = OrderPurchase::getAllPOData($id,$sellerId);


        abort_if(!$orderPurchase, 404);

        $addedProductCodes = [];
        foreach ($orderPurchase->order_purchase_details as $detail) {
            array_push($addedProductCodes, "'".$detail->product->product_code."'");
        }

        $products = Product::where('seller_id', $sellerId)->get();
        $suppliers = Supplier::where('seller_id', $sellerId)->get();


        $po_shipment_details = PoShipmentDetail:: where('order_purchase_id', $id)->get();

        $shiptypes = ShipType::all();
        $cargos = AgentCargoName::all();
        $domesticShippers = DomesticShipper::all();

        $shipping_marks  = AgentCargoMark::all();
        $payment  = PoPayments::getAllPaymentByPOID($id);

        $data = [
            'orderPurchase' => $orderPurchase,
            'addedProductCodes' => collect($addedProductCodes),
            'products' => ProductTypeAheadResource::collection($products),
            'suppliers' => $suppliers,
            'shiptypes' => $shiptypes,
            'cargos' => $cargos,
            'shipping_marks' => $shipping_marks,
            'domesticShippers' => $domesticShippers,
            'exchangeRates' => ExchangeRate::all(),
            'payment' => $payment,
        ];

        return view('seller.purchase_order.edit', $data);
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {

    }
    public function orderPurchaseDelete(Request $request)
    {


        $orderPurchase = OrderPurchase::find($request->id);
        $orderPurchase->delete();

        // Delete Order Purchase Detail  By order purchase ID & Seller ID
        OrderPurchaseDetail::deletePoDetailsByID($request->id,Auth::user()->id);


        // Delete PO Shipment By order purchase ID
        $poShipment = PoShipment::deletePOShipmentByOrderPurchaseID($request->id);

        // Delete PO Shipment Details  By order purchase ID
        PoShipmentDetail::deletePoShipmentDetailsByPOID($request->id);

        return [
        'status' => 1
        ];
    }


    public function changeOrderPurchaseStatus(Request $request)
    {

       $orderPurchase = OrderPurchase::find($request->id);
       $orderPurchase->status = $request->status;
       $orderPurchase->save();

       $orderPurchaseDetails = OrderPurchaseDetail::updatePOStatusByPOId($orderPurchase->id,$request->status);

       if($orderPurchase) {
        return redirect('order_purchase')->with('success','Order Purchase Status Updated Successfully');
        }
        else{
            return redirect('order_purchase')->with('danger','Something happened wrong');
        }

    }


    public function getShippingMarkByShippingTypeId(Request $request)
    {

        $shipping_marks  = AgentCargoMark::getTableDataByColumnValue('ship_type_id',$request->ship_type_id);
       // Ajax Reponses data
        $html = '<option>All Shipping Mark </option>';
        foreach($shipping_marks as $shipping_mark){
            $html .= '<option value="'.$shipping_mark->id.'">'.$shipping_mark->shipping_mark.'</option>';
        }
        return $html;
    }

    /**
     * Handle datatable serverside
     *
     * @param  \App\Http\Requests\DatatableRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function data(DatatableRequest $request)
    {
        $start = $request->get('start', 0);
        $limit = $request->get('length', 10);

        $search = isset($request->get('search')['value'])
            ? $request->get('search')['value']
            : null;

        if ($request->get('supplier_name')){
            $search = $request->get('supplier_name');
        }

        $orderColumnList = [
            'order_date',
            'e_a_d_f',
            'supplier_name',
            'author_name',
            'status'
        ];

        $orderColumnIndex = isset($request->get('order')[0]['column'])
            ? $request->get('order')[0]['column']
            : 0;

        $orderDir = isset($request->get('order')[0]['dir'])
            ? $request->get('order')[0]['dir']
            : 'asc';

        $orderColumn = isset($orderColumnList[$orderColumnIndex])
            ? $orderColumnList[$orderColumnIndex]
            : 'id';

        $statusFilter = $request->get('status');
        $supplierId = $request->get('supplierId', 0);
        $arrive_or_over_due = $request->get('arrive_or_over_due', NULL);

        // This will be used as a parameter to get data
        $dt_params['sellerId'] = Auth::user()->id;
        $dt_params['statusFilter'] = $statusFilter;
        $dt_params['supplierId'] = $supplierId;
        $dt_params['arrive_or_over_due'] = $arrive_or_over_due;
        $dt_params['days_limit'] = 3;
        $dt_params['search'] = $search;
        $dt_params['orderColumn'] = $orderColumn;
        $dt_params['orderDir'] = $orderDir;
        $dt_params['limit'] = $limit;
        $dt_params['start'] = $start;

        $orderPurchases = OrderPurchase::getDataForDatatable($dt_params);

        /* This is required to find total status count */
        $dt_params['statusFilter'] = 'all';
        $orderPurchaseTotalCountByStatus = OrderPurchase::getOrderPurchaseTotal($dt_params);
        $countAll = 0;

        if (!empty($orderPurchaseTotalCountByStatus)) {
            foreach($orderPurchaseTotalCountByStatus as $item){
                $orderPurchasesCount[$item->status] = $item->total;
                $countAll += $item->total;
            }

            $orderPurchasesCount['all'] = $countAll;
        }

        $orderPurchaseTotal = $countAll;
        if ($request->get('status') == OrderPurchaseStatusEnum::open()->value){
            $orderPurchaseTotal = isset($orderPurchasesCount['open']) ? $orderPurchasesCount['open'] : 0 ;
        }

        if ($request->get('status') == OrderPurchaseStatusEnum::arrive()->value) {
            $orderPurchaseTotal = isset($orderPurchasesCount['arrive']) ? $orderPurchasesCount['arrive'] : 0 ;
        }

        if ($request->get('status') == OrderPurchaseStatusEnum::close()->value) {
            $orderPurchaseTotal = isset($orderPurchasesCount['close']) ? $orderPurchasesCount['close'] : 0 ;
        }

        return Datatables::of($orderPurchases)
            ->addColumn('date', function ($row) {
                $orderDate = ($row->order_date != null) ? $row->order_date->format('d M Y') : '-';
                $createdDate = ($row->created_at != null) ? $row->created_at->format('d M Y') : '-';

                return '
                    <span class="whitespace-nowrap cursor-pointer hover:text-blue-500">
                        <a href="'. route('order_purchase.edit', [ 'order_purchase' => $row ]) .'"
                        class="underline text-blue-500 font-bold">
                            Order No: #'.$row->id.'
                        </a>
                        <br>
                        Order Date : <strong>'. $orderDate .'</strong>
                    </span>
                    <br>
                    <span class="whitespace-nowrap cursor-pointer hover:text-blue-500">
                        Created : <strong> '. $createdDate .'</strong>
                    </span>
                ';
            })
            ->addColumn('estimate_arrival', function ($row) {
                $estimatedArriveDate = '';

                if ($row->pos_e_a_d_f != null) {
                    $estimatedArriveDate .= '
                        <span class="whitespace-nowrap cursor-pointer hover:text-blue-500">
                            From: <strong>'. date('d M Y', strtotime($row->pos_e_a_d_f)) .'</strong>
                        </span><br>
                    ';
                }

                if ($row->pos_e_a_d_t != null) {
                    $estimatedArriveDate .= '
                        <span class="whitespace-nowrap cursor-pointer hover:text-blue-500">
                            To: <strong>'. date('d M Y', strtotime($row->pos_e_a_d_t)) .'</strong>
                        </span>
                    ';
                }

                return $estimatedArriveDate ;
            })
            ->addColumn('details', function ($row) {
                return '
                    <span class="whitespace-nowrap cursor-pointer hover:text-blue-500">
                        Supplier Name: <strong>'. $row->supplier_name .'</strong>
                    </span>
                    <br>
                    <span class="whitespace-nowrap cursor-pointer hover:text-blue-500">
                        Type: <strong>Import</strong>
                    </span>
                    <br>
                    <span class="cursor-pointer hover:text-blue-500;" style="word-break: break-all;">
                        Factory Tracking: <strong style="word-break: break-all;">'. $row->pos_factory_tracking .'</strong>
                    </span>
                    <br>
                    <span class="whitespace-nowrap cursor-pointer hover:text-blue-500">
                        Cargo Reference: <strong>'. $row->pos_cargo_ref .'</strong>
                    </span>
                    <br>
                    <span class="whitespace-nowrap cursor-pointer hover:text-blue-500">
                        No. Cartoons: <strong>'. $row->pos_number_of_cartons .'</strong>
                    </span>
                    <br>
                    <span class="whitespace-nowrap cursor-pointer hover:text-blue-500">
                        Domestic Logistics: <strong>'. $row->domestic_logistics .'</strong>
                    </span>
                    <br>
                    <span class="cursor-pointer hover:text-blue-500">
                        SKU: <strong>'. $row->products_code .'</strong>
                    </span>
                ';
            })
            ->addColumn('author_name', function ($row) {
                return $row->author_name ;
            })
            ->addColumn('status', function ($row) {
                $status = '<span class="badge-status--red">'. $row->status .'</span>';

                if ($row->status == OrderPurchaseStatusEnum::arrive()->value) {
                    $status = '<span class="badge-status--blue">'. $row->status .'</span>';
                }

                if ($row->status == OrderPurchaseStatusEnum::open()->value) {
                    $status = '<span class="badge-status--green">'. $row->status .'</span>';
                }

                if ($row->status == OrderPurchaseStatusEnum::close()->value) {
                    $status = '<span class="badge-status--yellow">'. $row->status .'</span>';
                }

                return  $status;
            })
            ->addColumn('action', function ($row) {
                return '
                    <a href="'. route('order_purchase.show', [ 'order_purchase' => $row ]) .'"
                        class="btn-action--green">
                        &nbsp;&nbsp;<i class="fas fa-info"></i>&nbsp;&nbsp;
                    </a>
                    <br>
                    <button type="button"
                        class="btn-action--blue"
                        id="purchase_status_chnage"
                        data-id="' . $row->id . '">
                        <i class="fas fa-shipping-fast"></i>
                    </button>
                    <a href="'. route('order_purchase.edit', [ 'order_purchase' => $row ]) .'"
                        class="btn-action--yellow">
                        &nbsp;<i class="fas fa-pencil-alt"></i>&nbsp;
                    </a>
                    <br>
                    <button type="button"
                        class="btn-action--red"
                        id="BtnDelete"
                        data-id="' . $row->id . '">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                ';
            })
            ->rawColumns(['date', 'estimate_arrival', 'details', 'status', 'action'])
            ->skipPaging()
            ->with([
                'suppliersCountAll' =>  isset($orderPurchasesCount['all']) ? $orderPurchasesCount['all'] : 0,
                'suppliersCountOpen' => isset($orderPurchasesCount['open']) ? $orderPurchasesCount['open'] : 0,
                'suppliersCountArrive' => isset($orderPurchasesCount['arrive']) ? $orderPurchasesCount['arrive'] : 0,
                'suppliersCountClose' => isset($orderPurchasesCount['close']) ? $orderPurchasesCount['close'] : 0,
            ])
            ->setTotalRecords($orderPurchaseTotal)
            ->make(true);
    }

    /**
     * This will return data into PO Edit Page >> Shipment Details Tab
     * Page URL : .... order_purchase/{id}/edit
     * @return mixed
     */
    public function ShowShipmentTableOnPOeditPage(Request $request)
    {
        $sellerId = Auth::user()->id;
        $order_purchase_id = $request->get('order_purchase_id', 0);
        return PoShipment::poShipmentDetailsByOrderPurchaseID($order_purchase_id,$sellerId);
    }

    public function poShipmentEdit(Request $request)
    {
        $poShipment = PoShipment::poShipmentDetailsByID($request->id,Auth::user()->id);

        return $poShipment[0];
    }

    public function poShipmentDelete(Request $request)
    {
        // Delete PO Shipment By po shipment ID
        $poShipment = PoShipment::deleteByID($request->id);

        // Delete PO Shipment Details  By po shipment ID
        PoShipmentDetail::deleteByPoShipmentID($request->id);

        return [
        'status' => 1
        ];
    }





}
