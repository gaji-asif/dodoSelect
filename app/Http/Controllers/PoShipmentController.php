<?php

namespace App\Http\Controllers;

use App\Http\Requests\DatatableRequest;
use App\Models\Product;
use App\Models\OrderPurchase;
use App\Models\PoShipment;
use App\Models\DomesticShipper;
use App\Models\Supplier;
use App\Models\ShipType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Yajra\DataTables\DataTables;

class PoShipmentController extends Controller
{
    /**
     * Load `PO Shipment` Page >> URL : .../po_shipments
    */
    public function shipments()
    {
        Session::put('itemArray', []);

        $suppliers = Supplier::getSuppliersBySellerID(Auth::user()->id);
        $shipTypes = ShipType::all();

        $poShipmentTotalStatusCount = $this->PoShipmentCount($supplierId=NULL);
        $data = [
            'poShipmentTotalAll' =>  isset($poShipmentTotalStatusCount['all']) ? $poShipmentTotalStatusCount['all'] : 0,
            'poShipmentTotalOpen' => isset($poShipmentTotalStatusCount['open']) ? $poShipmentTotalStatusCount['open'] : 0,
            'poShipmentTotalArrive' => isset($poShipmentTotalStatusCount['arrive']) ? $poShipmentTotalStatusCount['arrive'] : 0,
            'poShipmentTotalClose' => isset($poShipmentTotalStatusCount['close']) ? $poShipmentTotalStatusCount['close'] : 0,
            'suppliers' => $suppliers,
            'shipTypes' => $shipTypes
        ];

        return view('seller.purchase_order.po_shipments', $data);
    }

    /**
     * Handle the `data_product_cost` datatable
     * Serverside Datatable
     *
     * @param  \App\Http\Requests\DatatableRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function listData(DatatableRequest $request)
    {
        $sellerId = Auth::user()->id;

        $supplierId = $request->get('supplier_id', 0);
        $PoStatus = $request->get('status', '');

        $offset = $request->get('start', 0);
        $limit = $request->get('length', 10);

        $search = isset($request->get('search')['value'])
            ? $request->get('search')['value']
            : null;

        $orderColumnList = [
            'order_purchase_id', 'e_a_d_f', 'supplier_name'
        ];

        $orderColumnIndex = isset($request->get('order')[0]['column'])
            ? $request->get('order')[0]['column']
            : 0;

        $orderColumnDir = isset($request->get('order')[0]['dir'])
            ? $request->get('order')[0]['dir']
            : 'desc';

        $orderColumnName = isset($orderColumnList[$orderColumnIndex])
            ? $orderColumnList[$orderColumnIndex]
            : $orderColumnList[0];

        $otherReportParams = [
            'search' => $search,
            'limit' => $limit,
            'offset' => $offset,
            'order_column' => $orderColumnName,
            'order_dir' => $orderColumnDir,
            'supplier_id' => $supplierId,
        ];

        $data = PoShipment::POShipmentTable($sellerId, $supplierId, $PoStatus, $otherReportParams);
        $TotalRecords = PoShipment::POShipmentTableCount($sellerId, $supplierId, $PoStatus, $otherReportParams);

        $poShipmentTotalStatusCount = $this->PoShipmentCount($supplierId);

        return DataTables::of($data)
            ->addColumn('created_at', function ($row) {
                $createdDate = ($row->created_at != null) ? $row->created_at : '-';

                return $createdDate;
            })
            ->addColumn('date', function ($row) {
                $orderDate = ($row->order_date != null) ? $row->order_date : '-';
                $shipDate = ($row->ship_date != null) ? $row->ship_date : '-';
                $createdDate = ($row->created_at != null) ? $row->created_at : '-';

                return '
                    <a href="'. route('order_purchase.edit', [ 'order_purchase' => $row->order_purchase_id ]) .'"
                    class="underline text-blue-500 font-bold">
                        Order No: #'.$row->order_purchase_id.'
                    </a><br>

                    <span class="whitespace-nowrap cursor-pointer hover:text-blue-500">
                        Shipment ID: <strong>'. $row->id .'</strong>
                    </span><br>
                    <span class="whitespace-nowrap cursor-pointer hover:text-blue-500">
                        Order: <strong>'. $orderDate .'</strong>
                    </span><br>

                    <span class="whitespace-nowrap cursor-pointer hover:text-blue-500">
                        Ship: <strong>'. $shipDate .'</strong>
                    </span><br>
                    <span class="whitespace-nowrap cursor-pointer hover:text-blue-500">
                        Created : <strong> '. $createdDate .'</strong>
                    </span>
                ';
            })
            ->addColumn('arrival_date', function ($row) {
                $estimatedArriveDate = '<table class="w-full"><tbody>';

                    $estimatedArriveDateFrom = "N/A";
                    if ($row->e_a_d_f != null) {
                        $estimatedArriveDateFrom = $row->e_a_d_f;
                    }

                    $estimatedArriveDate .= '
                        <span class="whitespace-nowrap cursor-pointer hover:text-blue-500">
                            From: <strong>'. $estimatedArriveDateFrom .'</strong>
                        </span><br>
                    ';

                    $estimatedArriveDateTo = "N/A";
                    if ($row->e_a_d_t != null) {
                        $estimatedArriveDateTo = $row->e_a_d_t;
                    }

                    $estimatedArriveDate .= '
                        <span class="whitespace-nowrap cursor-pointer hover:text-blue-500">
                            To: <strong>'. $estimatedArriveDateTo .'</strong>
                        </span>
                    ';

                $estimatedArriveDate .= '</tbody></table>';

                return $estimatedArriveDate ;
            })
            ->addColumn('details', function ($row) {
                $details = '
                    <span class="whitespace-nowrap cursor-pointer hover:text-blue-500">
                        Supplier Name: <strong>'. $row->supplier_name .'</strong>
                    </span><br>
                    <span class="whitespace-nowrap cursor-pointer hover:text-blue-500">
                        Type: <strong>Import</strong>
                    </span><br>
                    <span class="cursor-pointer hover:text-blue-500;" style="word-break: break-all;">
                        Factory Tracking: <strong style="word-break: break-all;">'. $row->factory_tracking .'</strong>
                    </span><br>
                    <span class="whitespace-nowrap cursor-pointer hover:text-blue-500">
                        Cargo Reference: <strong>'. $row->cargo_ref .'</strong>
                    </span><br>
                    <span class="whitespace-nowrap cursor-pointer hover:text-blue-500">
                        No. Cartoons: <strong>'. $row->number_of_cartons .'</strong>
                    </span><br>
                    <span class="whitespace-nowrap cursor-pointer hover:text-blue-500">
                        Domestic Logistics: <strong>'. $row->domestic_logistics .'</strong>
                    </span><br>
                    <span class="cursor-pointer hover:text-blue-500">
                        SKU: <strong>'. $row->products_code .'</strong>
                    </span><br>';

                return $details;
            })
            ->addColumn('status', function ($row) {
                $status = '<span class="bg-red-200 text-red-700 text-xs px-2 rounded-md">'. $row->status .'</span>';

                if ($row->status == PoShipment::STATUS_ARRIVE) {
                    $status = '<span class="bg-green-200 text-green-700 text-xs px-2 rounded-md">'. $row->status .'</span>';
                }

                if ($row->status == PoShipment::STATUS_OPEN) {
                    $status = '<span class="bg-green-200 text-green-700 text-xs px-2 rounded-md">'. $row->status .'</span>';
                }

                if ($row->status == PoShipment::STATUS_CLOSE) {
                    $status = '<span class="bg-yellow-200 text-yellow-700 text-xs px-2 rounded-md">'. $row->status .'</span>';
                }

                return  $status;
            })
            ->addColumn('action', function ($row) {
                return '<div class="pt-2 mb-0 ">
                        <button type="button" class="modal-open btn-action--green BtnEditShipment" x-on:click="showEditModal=true"  data-order_purchase_id="'.$row->order_purchase_id.'"  data-id="'.$row->id.'" >
                            <i class="fas fa-pencil-alt"></i>
                        </button>
                        <button type="button" class="btn-action--red BtnDelete" data-id="' . $row->id . '">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </div>';
            })
            ->rawColumns(['created_date', 'date', 'arrival_date', 'details', 'status', 'action'])
            ->skipPaging()
            ->with([
                'CountAll' =>  isset($poShipmentTotalStatusCount['all']) ? $poShipmentTotalStatusCount['all'] : 0,
                'CountOpen' => isset($poShipmentTotalStatusCount['open']) ? $poShipmentTotalStatusCount['open'] : 0,
                'CountArrive' => isset($poShipmentTotalStatusCount['arrive']) ? $poShipmentTotalStatusCount['arrive'] : 0,
                'CountClose' => isset($poShipmentTotalStatusCount['close']) ? $poShipmentTotalStatusCount['close'] : 0,
                'search' => $search,
            ])
            ->setTotalRecords($TotalRecords)
            ->make(true);
    }


    public function SinglePOEditForm(Request $request)
    {
        if ($request->ajax()){

            if (isset($request->id) && $request->id != null) {
                $order_purchase_id = $request->order_purchase_id;
                $id = $request->id;

                $orderPurchase = OrderPurchase::where('id', $order_purchase_id)
                        ->where('seller_id', Auth::user()->id)

                        ->with(['order_purchase_details' => function($detail) {
                            $detail->with('product')
                                ->with('exchange_rate');
                        }])->with('supplier')
                        ->first();


                $po_shipments = PoShipment::where('id', $id)
                            ->where('seller_id', Auth::user()->id)
                            ->with('supplier')
                            ->with('order_purchase_details')
                            ->with([ 'po_shipment_details' => function($detail) {
                                $detail->with('product')
                                ->with('getShipped');
                            }])
                            ->get()->toArray();

                $domesticShippers = DomesticShipper::all();

                return view('seller.purchase_order.edit-po-shipment', compact(['orderPurchase','po_shipments','domesticShippers','order_purchase_id','id']));
            }
        }
    }


    private function PoShipmentCount($supplierId){
        $poShipmentTotalCountByStatus = PoShipment::poShipmentTotalCountByStatus(Auth::user()->id,$supplierId);
        $countAll = 0;
         if(!empty($poShipmentTotalCountByStatus)){
             foreach($poShipmentTotalCountByStatus as $item){
                $poShipmentTotalStatusCount[$item->status] = $item->total;
                $countAll += $item->total;
             }
         }

        $poShipmentTotalStatusCount['all'] = $countAll;

        return $poShipmentTotalStatusCount;
    }
    public function PoShipmentDelete(Request $request)
    {
        $PoShipment = DB::table('po_shipments')->where('id', $request->id)->delete();

        DB::table('po_shipment_details')->where('po_shipment_id', $request->id)->delete();

        if($PoShipment)
        {
             return [
                'status' => 1
             ];
        }


    }


    // Delete Exisiting data from po_shipment_details by po_shipment_id
    public static function deleteByPoShipmentID($po_shipment_id){
        DB::table('po_shipment_details')->where('po_shipment_id', $po_shipment_id)->delete();
    }

   /**
     * Delete From `order_purchase_details` table By order_purchase_id
     *
     * @return
     */
    public static function deletePoShipmentDetailsByPOID($order_purchase_id)
    {
        DB::table('po_shipment_details')->where('order_purchase_id', $order_purchase_id)->delete();
    }



    public function LoadPOShipmentEditForm(Request $request)
    {
        if ($request->ajax()){

            if (isset($request->id) && $request->id != null) {
                $id = $request->id;
                $po_shipments  = DB::table('po_shipments')->where('order_purchase_id', $id)->first();
                $po_shipment_details  = DB::select(DB::raw("SELECT * FROM(
                    SELECT POSD.*,SUM(POSD.ship_quantity) AS total_ship_qty,P.product_name,P.product_code,P.image
                    FROM `po_shipment_details` POSD
                    LEFT JOIN `products` P ON P.id=POSD.product_id
                    WHERE POSD.order_purchase_id = {$id}
                    GROUP BY POSD.product_id
                    ) tb1
                    "));
                $domesticShippers = DomesticShipper::all();
                return view('seller.purchase_order.form-edit-po-shipment', compact(['po_shipments', 'po_shipment_details','domesticShippers','id']));
            }
        }
    }

    public function updatePoShipment(Request $request)
    {
        $editid = $request->id;

        if($editid !='') {
            $updated_at = date('Y-m-d');

            $e_a_d_f = null;
            if (!empty($request->e_a_d_f)) {
                $e_a_d_f = date('Y-m-d', strtotime($request->e_a_d_f));
            }

            $e_a_d_t = null;
            if (!empty($request->e_a_d_t)) {
                $e_a_d_t = date('Y-m-d', strtotime($request->e_a_d_t));
            }

            $ship_date = null;
            if (!empty($request->ship_date)) {
                $ship_date = date('Y-m-d', strtotime($request->ship_date));
            }

            $data_ship = array(
                'factory_tracking' => $request->factory_tracking ? $request->factory_tracking: '-',
                'shipping_type_id' => $request->shipping_type_id ? $request->shipping_type_id: '-',
                'agent_cargo_id' => $request->agent_cargo_id ? $request->agent_cargo_id: '-',
                'shipping_mark_id' => $request->shipping_mark_id ? $request->shipping_mark_id: '-',
                'domestic_shipper_id' => $request->domestic_shipper_id ? $request->domestic_shipper_id: '-',
                'cargo_ref' => $request->cargo_ref,
                'e_a_d_f' => $e_a_d_f,
                'e_a_d_t' => $e_a_d_t,
                'ship_date' => $ship_date,
                'status' => $request->status,
                'number_of_cartons' => $request->number_of_cartons ? $request->number_of_cartons: '-',
                'domestic_logistics' => $request->domestic_logistics ? $request->domestic_logistics: '-',
                "updated_at" => $updated_at,
                "seller_id" => Auth::user()->id,
            );

            // Call updateData() method of Product Model
            POShipment::updateDataPOShipment($editid, $data_ship);

            DB::table('po_shipment_details')->where('po_shipment_id',$request->po_shipment_id)->delete();

            if (!empty($request->product_id)) {
                foreach($request->product_id as $key => $product_id ) {
                    $data_ship = array(
                        'po_shipment_id' => $request->po_shipment_id,
                         'order_purchase_id' => $editid,
                        'product_id' => $product_id,
                        'ship_quantity' => $request->ship_quantity[$key] ?? 0,
                        "updated_at" => $updated_at,
                        "seller_id" => Auth::user()->id,
                    );

                    $insertedId = OrderPurchase::insertTableData('po_shipment_details', $data_ship);
                }
            }

            return redirect('/po_shipments')->with('success','PO Shipment Updated Successfully');
        }

        return redirect('/po_shipments')->with('danger','Something has gone wrong');
    }


    public function changeProductCostPrice(Request $request)
    {
        $editid = $request->id;
        $cost_price = $request->input('cost_price');
        $ship_cost = $request->input('ship_cost');
        $cost_currency = $request->input('cost_currency');
        $lowest_value = $request->input('lowest_value');
        $supplier_id = $request->input('supplier_id');

        if($cost_price !='' || $ship_cost != ''|| $cost_currency != ''|| $lowest_value != ''|| $supplier_id != '') {
            $data = array(
                'cost_price' => $cost_price,
                 "ship_cost" => $ship_cost,
                 "cost_currency" => $cost_currency,
                 "lowest_value" => $lowest_value,
                 "supplier_id" => $supplier_id
                );

            // Call updateData() method of Product Model
            Product::updateData($editid, $data);
            return redirect('cost_analysis')->with('success','Product Cost Price Updated Successfully');
        }else{
            return redirect('cost_analysis')->with('danger','Something has gone wrong');
        }

    }

}
