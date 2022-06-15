<?php

namespace App\Http\Controllers;
use App\Models\ActivityLog;
use App\Models\StockLog;
use Datatables;
use DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Shop;
use App\Models\ProductPrice;
use App\Models\Category;
use App\Models\Supplier;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;


class ReportController extends Controller
{
    public function report()
    {
        $categories = Category::where('seller_id',Auth::user()->id)->get();
        $suppliers = Supplier::where('seller_id',Auth::user()->id)->get();


        $data = [
            'categories' => $categories,
            'suppliers' => $suppliers,
        ];

        return view('seller.report.report',$data);
    }


    /**
     * Handle the `product stock report` datatable
     * Serverside Datatable
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     *
     */
    public function reportData(Request $request)
    {
        if ($request->ajax()) {
            $sellerId = Auth::user()->id;

            $reportStatus = $request->get('status', array());
            $categoryId = $request->get('categoryId', '');

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
                ''
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
                'category_id' => $categoryId
            ];

            $data = Product::reportStockTable($sellerId, $reportStatus, $otherReportParams);
            $dataCount = Product::reportStockTableCount($sellerId, $reportStatus, $otherReportParams);

            $table = Datatables::of($data)
                        ->addColumn('image', function ($row) {
                            if (Storage::disk('s3')->exists($row->image) && !empty($row->image)){
                                return '<img src="'. Storage::disk('s3')->url($row->image) .'" class="w-28 h-auto">';
                            }
                            return '<img src="'. product_image_url($row->image) .'" class="w-28 h-auto">';
                        })
                        ->addColumn('quantity', function ($row) {
                            return '<a href="'. route('seller quantity details', [ 'id' => $row->id ]) .'" class="text-gray-800 underline">'
                                        . number_format($row->quantity) .
                                    '</a>' ;
                        })
                        ->addColumn('incoming', function ($row) {
                            return '<span x-on:click="showEditModal=true" style="cursor: pointer;" data-id="'. $row->id .'" class="modal-open custome_quantity" id="BtnProduct" href="#">'
                                        . number_format($row->total_incoming) .
                                    '</span>';
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
                        ->rawColumns(['image', 'quantity', 'incoming', 'report_status'])
                        ->skipPaging()
                        ->setTotalRecords($dataCount)
                        ->make(true);

            return $table;
        }
    }

    public function filterReport(Request $request)
    {
        $data1 =  DB::table("products")
            ->join('product_main_stocks','product_main_stocks.product_id','products.id')
            ->where('seller_id',Auth::user()->id)
            ->where(function($query) use($request){
                return $request->category ?
                    $query->from('products')->where('category_id',$request->category) : '';
            })
            ->get();

        $data = [];
        foreach($data1 as $row)
        {
            if(isset($request->status) && $request->status == 1){
                if($row->alert_stock != '' && $row->quantity <= 0)
                {
                    array_push($data,$row);
                }
            }
            elseif(isset($request->status) && $request->status == 2){
                if($row->quantity > 0 && $row->quantity <= $row->alert_stock){
                    array_push($data,$row);
                }
            }
            elseif(isset($request->status) && $request->status == 3){
                if($row->alert_stock != '' && $row->quantity > $row->alert_stock)
                {
                    array_push($data,$row);
                }
            }
            elseif(isset($request->status) && $request->status == 4){
                if($row->alert_stock == '')
                {
                    array_push($data,$row);
                }
            }
        }
        $table = Datatables::of($data)
            ->addColumn('image', function ($row) {
                if(!empty($row->image))
                {
                    return '<span><img src="'.asset($row->image).'" class="cutome_image" ></span>';
                }
            })
            ->addColumn('quantity', function ($row) {
                return '<a class="custome_quantity" href="'.url("seller/see-details/$row->id").'">'.$row->quantity.'</a>' ;
            })
            ->addColumn('incoming', function ($row) {
                $total_incoimg_products = DB::table('order_purchase_details')
                    ->where('product_id', '=', $row->id)
                    ->sum('quantity');

                return '<span x-on:click="showEditModal=true" style="cursor: pointer;" data-id="' . $row->id . '" class="modal-open custome_quantity" id="BtnProduct" href="#">'.$total_incoimg_products.'</span>';
            })
            ->addColumn('reprort_status', function ($row) {
                if($row->alert_stock != '' && $row->quantity <= 0)
                {
                    return '<span class="badge badge-pill badge-danger">Out 0f Stock</span>';
                }
                elseif($row->quantity > 0 && $row->quantity <= $row->alert_stock )
                {
                    return '<span class="badge  badge-pill badge-warning">Low Stock</span>';
                }
                elseif($row->alert_stock != '' &&  $row->quantity > $row->alert_stock )
                {
                    return '<span class="badge badge-pill badge-success">OVER STOCK</span>';
                }
                elseif($row->alert_stock == '' )
                {
                    return '<span class="">N/A</span>';
                }
            })
            ->rawColumns(['image','quantity','incoming','reprort_status'])
            ->make(true);
        return $table;
    }

    public function stockReport()
    {
        return view('seller.report.stock_movements');
    }


    public function stockReportData(Request $request)
    {
        if ($request->ajax()) {
            $sellerId = Auth::user()->id;

            $reportStatus = $request->get('status', array());
            $supplierId = $request->get('supplierId', '');
            $categoryId = $request->get('categoryId', '');

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
                'added',
                'removed',
                'net_change'
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


            $from_date = $to_date = '';
            if(!empty($request->from_date) && !empty($request->to_date)){
                $from_date = $request->from_date;
                $to_date = $request->to_date;
            } elseif (!empty($request->from_date) && empty($request->to_date)){
                $from_date = $request->from_date;
                $to_date = $request->from_date;
            } elseif (empty($request->from_date) && !empty($request->to_date)){
                $from_date = $request->to_date;
                $to_date = $request->to_date;
            }

            $otherReportParams = [
                'search' => $search,
                'limit' => $limit,
                'offset' => $offset,
                'order_column' => $orderColumn,
                'order_dir' => $orderColumnDir,
                'from_date' => $from_date,
                'to_date' => $to_date
            ];



            $data = StockLog::reportStockTable($sellerId, $otherReportParams);
            $dataCount = StockLog::reportStockTableCount($sellerId, $otherReportParams);

            $table = Datatables::of($data)
                        ->addColumn('image', function ($row) {
                            if (Storage::disk('s3')->exists($row->image) && !empty($row->image)){
                                return '<img src="'.Storage::disk('s3')->url($row->image).'" class="w-full h-auto">';
                            }
                            return '<img src="'. product_image_url($row->image) .'" class="w-28 h-auto">';
                        })
                        ->addColumn('added', function ($row) {
                           return number_format($row->added);
                        })
                        ->addColumn('removed', function ($row) {
                                return number_format($row->removed);

                        })

                        ->addColumn('net_change', function ($row) {
                            if (!empty($row->net_change)) {
                                return $row->net_change ;
                            }else{
                                return 0 ;
                            }
                        })

                        ->rawColumns(['image'])
                        ->skipPaging()
                        ->setTotalRecords($dataCount)
                        ->make(true);

            return $table;
        }
    }


    public function activityLog()
    {
        $data = ActivityLog::all();

        return view('seller.report.activity_log', compact('data'));
    }

    public function dataActivityLog(Request $request)
    {
        $data = [];

        $start = $request->get('start', 0);
        $limit = $request->get('length', 10);
        $search = isset($request->get('search')['value'])
            ? $request->get('search')['value']
            : null;

        $orderColumnIndex = isset($request->get('order')[0]['column'])
            ? $request->get('order')[0]['column']
            : 2;
        $orderDir = isset($request->get('order')[0]['dir'])
            ? $request->get('order')[0]['dir']
            : 'desc';

        $availableColumnsOrder = [
            'id', 'action', 'product_name', 'product_code', 'quantity', 'user_name', 'created_at'
        ];

        $orderColumnName = isset($availableColumnsOrder[$orderColumnIndex])
            ? $availableColumnsOrder[$orderColumnIndex]
            : $availableColumnsOrder[6];

        $fields = ActivityLog::with('product')
            ->userNameAsColumn()
            ->searchActivityLogTable($search)
            ->orderBy($orderColumnName, $orderDir)
            ->take($limit)
            ->skip($start)
            ->get();

        if (!empty($fields)) {
            foreach ($fields as $field) {
                $row = [];

                $row[] = $field->id;
                $row[] = $field->action;
                $row[] = $field->product_name;
                $row[] = $field->product_code;
                $row[] = number_format($field->quantity);
                $row[] = $field->user->name;
                $row[] = $field->created_at->format('Y-m-d H:i');
                $row[] = '
                    <button
                        type="button"
                        class="btn-action--red"
                        title="Undo"
                        id="UndoBtn"
                        data-id="'. $field->id .'"
                        onClick="undoActivityLog(this)">
                        <i class="fas fa-exchange-alt"></i>
                    </button>';

                $data[] = $row;
            }
        }

        $count_total = ActivityLog::whereHas('product')->count();
        $count_total_search = ActivityLog::searchActivityLogTable($search)->count();

        $response = [
            'draw' => $request->get('draw'),
            'recordsTotal' => $count_total,
            'recordsFiltered' => $count_total_search,
            'data' => $data
        ];

        return response()->json($response);

    }

    public function undoActivityLog(Request $request)
    {
        try {
            $activityLog = ActivityLog::where('id', $request->id)->first();
            ActivityLog::undoActivityLog($request->id);

            return response()->json(['status' => 1]);

        } catch (\Throwable $th) {
            report($th);

            return response()->json([ 'message' => $th->getMessage() ], 500);
        }
    }

}
