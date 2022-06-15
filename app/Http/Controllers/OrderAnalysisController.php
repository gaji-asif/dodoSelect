<?php

namespace App\Http\Controllers;
use App\Http\Requests\OrderPurchase\StoreRequest;
use App\Http\Resources\ProductTypeAheadResource;
use App\Imports\BulkImportPCost;
use App\Models\Product;
use App\Models\OrderPurchase;
use App\Models\PoShipment;
use App\Models\DomesticShipper;
use App\Models\ExchangeRate;
use App\Models\Category;
use App\Models\Supplier;
use App\Models\ShipType;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Datatables;
use DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB as FacadesDB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Facades\Excel;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Str;

use App\Exports\OrderAnalysisExport;

class OrderAnalysisController extends Controller
{


    /**
     * Load `Order Analysis` Page >> URL : .../order_analysis
    */
    public function orderAnalysis()
    {
        $categories = Category::where('seller_id',Auth::user()->id)->get();
        $suppliers = Supplier::where('seller_id',Auth::user()->id)->get();


        $data = [
            'categories' => $categories,
            'suppliers' => $suppliers,
        ];

        return view('seller.purchase_order.order-analysis', $data);
    }

    /**
     * Handle the `product stock report` datatable
     * Serverside Datatable
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     *
     */
    public function loadOrderAnalysisDataTable(Request $request)
    {
        if ($request->ajax()) {
            $sellerId = Auth::user()->id;

            $reportStatus = $request->get('status', array());
            $supplierId = $request->get('supplierId', '');
            $categoryId = $request->get('categoryId', '');
            $dateFrom = $request->get('dateFrom', '');
            $dateTo = $request->get('dateTo', '');

            $offset = $request->get('start', 0);
            $limit = $request->get('length', 10);
            if ($limit < 1 OR $limit > 100) {
                $limit = 100;
            }

            $search = isset($request->get('search')['value'])
                    ? $request->get('search')['value']
                    : null;

            $orderColumnList = [
                'id',
                 '',
                'product_name',
                'product_code',
                'quantity',
                'total_incoming',
                'alert_stock',
                'total_stock_in',
                'total_stock_out',
                'price',
                'lowest_sell_price'
            ];

            $orderColumnIndex = isset($request->get('order')[0]['column'])
                                ? $request->get('order')[0]['column']
                                : 0;
            $orderColumnDir = isset($request->get('order')[0]['dir'])
                                ? $request->get('order')[0]['dir']
                                : 'asc';

            $orderColumn = isset($orderColumnList[$orderColumnIndex])
                            ? $orderColumnList[$orderColumnIndex]
                            : 'product_name';

            $otherReportParams = [
                'search' => $search,
                'limit' => $limit,
                'offset' => $offset,
                'order_column' => $orderColumn,
                'order_dir' => $orderColumnDir,
                'supplier_id' => $supplierId,
                'category_id' => $categoryId,
                'dateFrom' => $dateFrom,
                'dateTo' => $dateTo
            ];


            $data = Product::reportStockTable($sellerId, $reportStatus, $otherReportParams);
            $dataCount = Product::reportStockTableCount($sellerId, $reportStatus, $otherReportParams);

            if(isset($request->excel)){
                $excelParams = [
                    'search' => $request->get('excelSearch', ''),
                    'limit' => '-1', // for all
                    'offset' => '0',
                    'order_column' => $orderColumn,
                    'order_dir' => $orderColumnDir,
                    'supplier_id' => $supplierId,
                    'category_id' => $categoryId,
                    'dateFrom' => $dateFrom,
                    'dateTo' => $dateTo
                ];

                $dataExcel = Product::reportStockTable($sellerId, $reportStatus, $excelParams);

                $i=1;
                $excel_data = [];
                foreach($dataExcel as $key=>$row)
                    {

                        $excel_data[$key]['sl']= $i++;
                        $excel_data[$key]['product_name']= $row->product_name;
                        $excel_data[$key]['product_code']= $row->product_code;
                        $excel_data[$key]['quantity']= $row->quantity;
                        $excel_data[$key]['Incoming']= $row->total_shipped;
                        $excel_data[$key]['alert_stock']= $row->alert_stock;
                        if ($row->id) {
                            $reorders = DB::select(DB::raw("
                            SELECT PR.*,ST.name as ship_type
                            FROM `product_reorders` PR
                            LEFT JOIN `ship_types` ST ON ST.id=PR.type
                            WHERE PR.product_id=$row->id
                            "));
                            $list = '';
                            foreach($reorders as $reorder){
                                $status = ucwords(str_replace("_", " ", $reorder->status));
                                $list .= $status.' '.$reorder->ship_type.' ('.$reorder->quantity.') ';

                            }
                            $excel_data[$key]['ordered_qty']=  $row->total_ordered_qty ;
                            $excel_data[$key]['stock_in']=  $row->total_stock_in ;
                            $excel_data[$key]['stock_out']=  $row->total_stock_out ;
                            $excel_data[$key]['price']=  $row->price ;
                            $excel_data[$key]['lowest_sell_price']=  $row->lowest_sell_price ;

                            $product_costs = DB::select(DB::raw("
                                SELECT PC.*,E.name as currency_name,S.supplier_name
                                FROM `product_costs` PC
                                LEFT JOIN `suppliers` S ON S.id=PC.supplier_id
                                LEFT JOIN `exchange_rate` E ON E.id=PC.exchange_rate_id
                                WHERE PC.product_id=$row->id
                                "));
                                $suppliers ='-';
                                foreach($product_costs as $product_cost){
                                    if($product_cost->default_supplier==1){
                                        $sign="*";
                                    }else{
                                        $sign="";
                                    }
                                        $suppliers .= $product_cost->cost.' '.$product_cost->currency_name.' ('.$product_cost->supplier_name.')'.$sign.' / ';

                                }

                                //$excel_data[$key]['suppliers']=  $suppliers ;

                                $report_status = '-';
                                if (trim($row->alert_stock) == '') {
                                    $report_status = 'N/A';
                                }

                                if ($row->alert_stock >= 0 && $row->quantity > $row->alert_stock) {
                                    $report_status = 'OVER STOCK';
                                }

                                if ($row->alert_stock >= 0 && $row->quantity <= $row->alert_stock && $row->quantity > 0) {
                                    $report_status = 'Low Stock';
                                }

                                if ($row->alert_stock >= 0 && $row->quantity <= 0) {
                                    $report_status = 'Out 0f Stock';
                                }

                                $excel_data[$key]['report_status']=  $report_status ;

                        }
                    }

                $export_order_analysis = new OrderAnalysisExport($excel_data,$dateFrom,$dateTo);
                $excel = Excel::download($export_order_analysis, 'order-analysis.xlsx');
                $excel->setContentDisposition('attachment','order-analysis')->getFile()->move(public_path('/order-analysis'), 'order-analysis'.time().'.xlsx');
                return asset('order-analysis').'/order-analysis'.time().'.xlsx';
            }


            $table = Datatables::of($data)
                        ->addColumn('image', function ($row) {
                            if (Storage::disk('s3')->exists($row->image) && !empty($row->image)){
                                return '<img src="'. Storage::disk('s3')->url($row->image) .'" class="w-28 h-auto">';
                            }
                            return '<img src="'. product_image_url($row->image) .'" class="w-28 h-auto">';
                        })
                        ->addColumn('quantity', function ($row) {

                            return  number_format($row->quantity) ;
                        })

                        ->addColumn('price', function ($row) {
                            if(is_numeric($row->price)){
                                return  number_format($row->price) ;
                            }else{
                                return  '' ;
                            }
                        })

                        ->addColumn('lowest_sell_price', function ($row) {
                            if(is_numeric($row->lowest_sell_price)){
                                return  number_format($row->lowest_sell_price) ;
                            }else{
                                return  '' ;
                            }

                        })

                        ->addColumn('total_incoming', function ($row) {
                            if(is_numeric($row->total_shipped)){
                                return '<button type="button" class="bg-transparent border-0 underline outline-none focus:outline-none" id="BtnShowIncoming" data-id="'. $row->id .'">
                            '. number_format($row->total_shipped) .'
                                </button>';
                            }
                        })

                        ->addColumn('ordered_qty', function ($row) {
                            if (!empty($row->total_ordered_qty)) {
                                return $row->total_ordered_qty ;
                            }else{
                                return 0 ;
                            }

                        })

                        ->addColumn('total_stock_in', function ($row) {
                            if (!empty($row->total_stock_in)) {
                                return $row->total_stock_in ;
                            }else{
                                return 0 ;
                            }

                        })

                        ->addColumn('total_stock_out', function ($row) {
                            if (!empty($row->total_stock_out)) {
                                return $row->total_stock_out ;
                            }else{
                                return 0 ;
                            }

                        })

                        ->addColumn('product_cost_suppliers', function ($row) {
                            if (!empty($row->product_cost)) {
                                $list ='';
                                $Arr_product_cost_default_supplier = explode("##",$row->product_cost_default_supplier);
                                $Arr_product_cost = explode("##",$row->product_cost);
                                $Arr_product_cost_currencies = explode("##",$row->product_cost_currencies);
                                $Arr_product_cost_suppliers = explode("##",$row->product_cost_suppliers);

                                foreach($Arr_product_cost as $index=>$product_cost){
                                    if($Arr_product_cost_default_supplier[$index]==1){
                                        $sign="*";
                                    }else{
                                        $sign="";
                                    }
                                        $list .= '<p class="mb-2">'.$product_cost.' '.$Arr_product_cost_currencies[$index].' ('.$Arr_product_cost_suppliers[$index].')<span class="font-bold">'.$sign.'</span></p>';

                                }
                                return $list ;

                            }
                        })

                        ->addColumn('report_status', function ($row) {
                            if (trim($row->alert_stock) == '') {
                                return '<span class="badge badge-pill badge-secondary">N/A</span>';
                            }

                            if ($row->alert_stock >= 0 && $row->quantity > $row->alert_stock) {
                                return '<span class="badge badge-pill badge-success">OVER STOCK</span>';
                            }

                            if ($row->alert_stock >= 0 && $row->quantity <= $row->alert_stock && $row->quantity > 0) {
                                return '<span class="badge badge-pill badge-warning">Low Stock</span>';
                            }

                            if ($row->alert_stock >= 0 && $row->quantity <= 0) {
                                return '<span class="badge badge-pill badge-danger">Out 0f Stock</span>';
                            }

                            return '<span class="badge badge-pill badge-secondary">N/A</span>';
                        })

                        ->rawColumns(['image', 'quantity', 'total_incoming', 'reorder_qty','product_cost_suppliers','report_status'])
                        ->skipPaging()
                        ->setTotalRecords($dataCount)
                        ->make(true);

            return $table;
        }
    }

}
