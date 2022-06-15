<?php

namespace App\Http\Controllers;
use App\Http\Requests\OrderPurchase\StoreRequest;
use App\Http\Resources\ProductTypeAheadResource;
use App\Imports\BulkImportPCost;
use App\Models\Product;
use App\Models\ProductMainStock;
use App\Models\ProductCost;
use App\Models\StockLog;
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
class ProductCostController extends Controller
{
    public function index()
    {
        ///return view('seller.purchase_order.index');
    }

    public function productCost()
    {
        $suppliers = Supplier::all();
        $shipTypes = ShipType::all();
        $data = [
            'suppliers' => $suppliers,
            'shipTypes' => $shipTypes
        ];

        return view('seller.purchase_order.product_cost', $data);
    }

    /**
     * Handle the `data_product_cost` datatable
     * Serverside Datatable
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     *
     */
    public function listData(Request $request)
    {
        if ($request->ajax()) {
            $sellerId = Auth::user()->id;

            $supplierId = $request->get('supplierId', 0);

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
                'supplier_id',
                'lowest_value',
                'operation_cost',
                ''
            ];


            $orderColumnIndex = isset($request->get('order')[0]['column'])
                ? $request->get('order')[0]['column']
                : 5;
            $orderColumnDir = isset($request->get('order')[0]['dir'])
                ? $request->get('order')[0]['dir']
                : 'asc';

            $orderColumnName = isset($orderColumnList[$orderColumnIndex])
                ? $orderColumnList[$orderColumnIndex]
                : $orderColumnList[5];

            $otherReportParams = [
                'search' => $search,
                'limit' => $limit,
                'offset' => $offset,
                'order_column' => $orderColumnName,
                'order_dir' => $orderColumnDir,
                'supplier_id' => $supplierId,
            ];

            $data = ProductCost::ProductCostTable($sellerId, $otherReportParams);
            $count_total = ProductCost::ProductCostTableCount($sellerId,$otherReportParams);

            $table = Datatables::of($data)
                ->addColumn('image', function ($row) {
                    if (Storage::disk('s3')->exists($row->image) && !empty($row->image)){
                        return '<img src="'. Storage::disk('s3')->url($row->image) .'" class="w-28 h-auto">';
                    }
                    return '<img src="'. product_image_url($row->image) .'" class="w-28 h-auto">';
                })
                ->addColumn('supplier_name', function ($row) {
                    if ($row->id) {
                        $product_costs = DB::select(DB::raw("
                                SELECT PC.*,E.name as currency_name,S.supplier_name
                                FROM `product_costs` PC
                                LEFT JOIN `suppliers` S ON S.id=PC.supplier_id
                                LEFT JOIN `exchange_rate` E ON E.id=PC.exchange_rate_id
                                WHERE PC.product_id=$row->id
                                "));
                        $list ='';
                        foreach($product_costs as $product_cost){
                            if($product_cost->default_supplier==1){$sign="*";}else{$sign=" ";}
                            $list .= '<p class="mb-2">'.$product_cost->cost.' '.$product_cost->currency_name.' ('.$product_cost->supplier_name.')<span class="font-bold">'.$sign.'</span></p>';
                        }
                        return $list ;
                    }
                })

                ->addColumn('lowest_value', function ($row) {
                    if ($row->lowest_value) {
                        if($row->lowest_is_type==0){
                          $type = "(By Manual)";
                        }else{
                            $type = "(By %)";
                        }

                        return '<div class="pt-2 mb-0 ">
                                    <p class="mb-0"><strong>'.$type.'</strong></p>
                                    <p class="mb-0"><strong>Lowest Sell Price : </strong> '.$row->lowest_sell_price.'</p>
                                    <p class="mb-0"><strong>Profit (THB) : </strong> '.$row->profit.'</p>
                                    <p class="mb-0"><strong>Mark Up (%) : </strong> '.$row->mark_up.'</p>
                                </div>';
                    }
                })

                ->addColumn('reorder', function ($row) {
                    if ($row->id) {
                        $reorders = DB::select(DB::raw("
                                SELECT PR.*,ST.name as ship_type
                                FROM `product_reorders` PR
                                LEFT JOIN `ship_types` ST ON ST.id=PR.type
                                WHERE PR.product_id=$row->id
                                "));
                        $list ='';
                        foreach($reorders as $reorder){
                            $status = ucwords(str_replace("_", " ", $reorder->status));
                            $list .= '<p class="mb-2">'.$status.' '.$reorder->ship_type.' ('.$reorder->quantity.')</p>';
                        }
                        return $list ;
                    }
                })
                ->addColumn('action', function ($row) {
                    return '<div class="pt-2 mb-0 ">
                                <a href='.route('create product cost', [ 'product_code' => $row->product_code,'product_id' => $row->id  ]) .' class="modal-open btn-action--green" x-on:click="showEditModal=true"  id="BtnProduct">
                                    <i class="fas fa-pencil-alt"></i>
                                </a>

                                <button type="button" class="modal-open btn-action--yellow BtnProductReOrder" x-on:click="showEditModal=true" data-id="'.$row->id.'">
                                <i class="fas fa-bars"></i>
                                </button>
                                </div>';
                                /*
                                <button type="button" class="modal-open btn-action--blue" x-on:click="showEditModal=true" data-id="'.$row->id.'"  id="BtnProductCost">
                                <i class="fas fa-money-bill"></i>
                                </button>
                                */
                })
                ->rawColumns(['image','supplier_name','lowest_value','reorder','action'])
                ->skipPaging()
                ->setTotalRecords($count_total)
                ->make(true);

            return $table;
        }
    }


    /**
     * Store `product_costs` data
     *
     * @param  \App\Http\Requests\OrderPurchase\StoreProductCostRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function storeProductCost(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'supplier_id' => 'required',
            'product_id' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Field id is required. '
            ], 500);
        }
        try {

            DB::table('product_costs')->where('product_id', $request->product_id[0])->delete();

            if (!empty($request->supplier_id)) {
                foreach($request->supplier_id as $key => $row ) {
                    $productCost = new ProductCost();

                    $productCost->product_id =  isset($request->product_id[$key]) ? $request->product_id[$key] : 0;
                    $productCost->default_supplier =  $request->default_supplier[$key];
                    $productCost->supplier_id = isset($request->supplier_id[$key]) ? $request->supplier_id[$key] : 0;
                    $productCost->cost = isset($request->cost[$key]) ? $request->cost[$key] : 0;
                    $productCost->exchange_rate_id = isset($request->exchange_rate_id[$key]) ? $request->exchange_rate_id[$key] : 0;
                    $productCost->pieces_per_pack = isset($request->pieces_per_pack[$key]) ? $request->pieces_per_pack[$key] : 0;
                    $productCost->pieces_per_carton = isset($request->pieces_per_carton[$key]) ? $request->pieces_per_carton[$key] : 0;
                    $productCost->operation_cost = isset($request->operation_cost[$key]) ? $request->operation_cost[$key] : 0;
                    $productCost->created_at = isset($request->created_at[$key]) ? $request->created_at[$key] : date('Y-m-d');
                    if ($request->hasFile('file')) {
                        $upload = $request->file[$key];
                        $upload_name =  time() . '_' . Str::random(10) . '_' . $upload->getClientOriginalName();
                        $destinationPath = public_path('uploads/product-cost');
                        $upload->move($destinationPath, $upload_name);
                        $productCost->file = 'uploads/product-cost/'.$upload_name;
                    }

                    $productCost->save();
                }

                $lowest_sell_price = $request->input('lowest_sell_price');
                $lowest_value = $request->input('lowest_value');
                $lowest_is_type = $request->input('lowest_is_type');
                $mark_up = $request->input('mark_up');
                $profit = $request->input('profit');
                if($lowest_value !='') {
                    $data = array(
                        'lowest_value' => isset($lowest_value) ? $lowest_value : 0,
                        'lowest_is_type' => isset($lowest_is_type) ? $lowest_is_type : 0,
                        'lowest_sell_price' => isset($lowest_sell_price) ? $lowest_sell_price : 0,
                        'mark_up' => isset($mark_up) ? $mark_up : 0,
                        'profit' => isset($profit) ? $profit : 0,
                    );
                    // Call updateData() method of Product Model
                    Product::updateData($request->product_id[0], $data);
                }

            }

            return response()->json([
                'message' => 'Product Cost Save Successfully.'
           ]);

        } catch (\Throwable $th) {
            report($th);

            return response()->json([
                'message' => 'Something went wrong. ' . $th->getMessage()
            ], 500);
        }
    }


    public function productCostAnalysis()
    {
        $data = Product::where('seller_id',Auth::user()->id)->get();

        if(session::has('product_data')) {
            $data = session::forget('product_data');
        }
        return view('seller.product_cost_price.index', compact('data'));
    }



    public function createProductCost(Request $request)
    {

        $product_code = $request->product_code;
        $product_id = $request->product_id;
        $sellerId = Auth::user()->id;

        $product = Product::where('product_code', $product_code)->get()->first();
        $product_costs = ProductCost::where('product_id', $product_id)->get();
        $suppliers = Supplier::where('seller_id', $sellerId)->get();



        $data = [
            'product' =>$product,
            'product_costs' =>$product_costs,
            'suppliers' => $suppliers,
            'exchangeRates' => ExchangeRate::where('seller_id', $sellerId)->orderBy('name', 'asc')->get()
        ];

        if(!empty($product)){
            return view('seller.purchase_order.create-product-cost', $data);
        }else{
            return redirect('product_cost')->with('error','This Product is Not Found....');
        }

    }

    /**
     * Handle the `markup and profit calculation` datatable
     * Serverside Datatable
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     *
     */
    public function markUpLowestSellPriceAndProfitCalculation(Request $request)
    {

        $product_id = $request->product_id;
        $lowest_is_type = $request->lowest_is_type;
        $lowest_value = $request->lowest_value ? $request->lowest_value : 0;
        $sellerId = Auth::user()->id;



            //$product = Product::where('id', $product_id)->get()->first();
            $product_costsDetails = ProductCost::where('product_id', $product_id)
            ->where('default_supplier', 1)
            ->first();

            $mark_up=0;
            $cost_price = 0;
            $rate = 0;
            $pieces_per_pack = 0;
            $ship_cost = 0;
            $profit = 'n/a';
            if($product_costsDetails ==null){
                //if default supplier not found then it will retireve any data.
                $product_costsDetails = ProductCost::where('product_id', $product_id)

            ->where('default_supplier', 0)
            ->first();
            }

            $cost_price = $product_costsDetails->cost;
            $pieces_per_pack = $product_costsDetails->pieces_per_pack;
            $ship_cost = $product_costsDetails->operation_cost;

            $exchange_rate_id = $product_costsDetails->exchange_rate_id;
            if($exchange_rate_id > 0){
                $exchange_rate = ExchangeRate::where('id', $exchange_rate_id)->get()->first();
            }else{
                $exchange_rate = ExchangeRate::where('id', 1)->get()->first();
            }

            $rate = $exchange_rate->rate;

            if($lowest_is_type == '1'){
                $mark_up = (int)$lowest_value;
                $lowest_sell_price =  (($cost_price * $pieces_per_pack * $ship_cost * $rate) * (1+($mark_up)/100));
                $profit = $lowest_sell_price - ($cost_price * $ship_cost * $pieces_per_pack * $rate) ;


            }else{
                $lowest_sell_price =  $lowest_value;
                $profit = $lowest_sell_price - ($cost_price * $pieces_per_pack * $ship_cost * $rate);
                $mark_up = ($profit / ($cost_price * $pieces_per_pack * $ship_cost * $rate))*100;
            }


            $data = [
                'cost_price' =>$cost_price,
                'ship_cost' =>$ship_cost,
                'exchange_rate' =>$rate,
                'mark_up' =>$mark_up,
                'lowest_sell_price' =>$lowest_sell_price,
                'profit' => $profit,
            ];



            return response()->json([
                'message' => 'success',
                'data' => $data
            ]);


    }



    /**
     * Handle the `data_product_cost` datatable
     * Serverside Datatable
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     *
     */
    public function POAnalysisData1(Request $request)
    {
        if ($request->ajax()) {
            $sellerId = Auth::user()->id;

            $orderByPrice = $request->get('orderByPrice', 'asc');
            $supplierId = $request->get('supplierId', 0);

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
                'supplier_id',
                'lowest_value',
                'operation_cost',
                ''
            ];

            $orderColumnIndex =  5;
            $orderColumnDir =  'asc';

            $orderColumn =  'lowest_value';

            $otherReportParams = [
                'search' => $search,
                'limit' => $limit,
                'offset' => $offset,
                'order_column' => $orderColumn,
                'order_dir' => $orderColumnDir,
                'supplier_id' => $supplierId,
                'orderByPrice' => $orderByPrice
            ];


            $data = Product::POAnalysisTable($sellerId, $otherReportParams);
            $dataCount = Product::POAnalysisTableCount($sellerId,$otherReportParams);

            $table = Datatables::of($data)
                        ->addColumn('id', function ($row) {
                            if ($row->id) {
                                return $row->id;
                            }
                        })
                        ->addColumn('image', function ($row) {
                            return '<img src="'. asset($row->image) .'" class="w-28 h-auto">';
                        })
                        ->addColumn('product_name', function ($row) {
                            if ($row->product_name) {
                                return $row->product_name;
                            }
                        })

                        ->addColumn('product_code', function ($row) {
                            if ($row->product_code) {
                                return $row->product_code;
                            }
                        })

                        ->addColumn('quantity', function ($row) {
                            if($row->quantity) {
                                return $row->quantity;
                            }
                        })

                        ->addColumn('incoming', function ($row) {
                            // ($row->quantity) {
                               // return $row->quantity;
                           // }
                        })

                        ->addColumn('supplier_name', function ($row) {
                            if ($row->id) {
                                $product_costs = DB::select(DB::raw("
                                SELECT PC.*,E.name as currency_name,S.supplier_name
                                FROM `product_costs` PC
                                LEFT JOIN `suppliers` S ON S.id=PC.supplier_id
                                LEFT JOIN `exchange_rate` E ON E.id=PC.exchange_rate_id
                                WHERE PC.product_id=$row->id
                                "));
                                $list ='';
                                foreach($product_costs as $product_cost){
                                    if($product_cost->default_supplier==1){$sign="*";}else{$sign=" ";}
                                    $list .= '<p class="mb-2">'.$product_cost->cost.' '.$product_cost->currency_name.' ('.$product_cost->supplier_name.')<span class="font-bold">'.$sign.'</span></p>';

                                }
                                return $list ;

                            }
                        })

                        ->addColumn('reorder', function ($row) {
                            if ($row->id) {
                                $reorders = DB::select(DB::raw("
                                SELECT PR.*,ST.name as ship_type
                                FROM `product_reorders` PR
                                LEFT JOIN `ship_types` ST ON ST.id=PR.type
                                WHERE PR.product_id=$row->id
                                "));
                                $list ='';
                                foreach($reorders as $reorder){
                                   $status = ucwords(str_replace("_", " ", $reorder->status));
                                    $list .= '<p class="mb-2">'.$status.' '.$reorder->ship_type.' ('.$reorder->quantity.')</p>';

                                }
                                return $list ;

                            }
                        })

                        ->addColumn('lowest_value', function ($row) {
                            if ($row->lowest_value) {
                                return $row->lowest_value;
                            }
                        })

                        ->addColumn('report_status', function ($row) {
                           //
                        })
                        ->addColumn('action', function ($row) {
                                return '<div class="pt-2 mb-0 ">
                                <a href='.route('create product cost', [ 'product_code' => $row->product_code,'product_id' => $row->id  ]) .' class="modal-open btn-action--green" x-on:click="showEditModal=true"  id="BtnProduct">
                                    <i class="fas fa-pencil-alt"></i>
                                </a>
                                <button type="button" class="modal-open btn-action--blue" x-on:click="showEditModal=true" data-id="'.$row->id.'"  id="BtnProductCost">
                                <i class="fas fa-money-bill"></i>
                                </button>
                                <button type="button" class="modal-open btn-action--yellow BtnProductReOrder" x-on:click="showEditModal=true" data-id="'.$row->id.'">
                                <i class="fas fa-bars"></i>
                                </button>
                                </div>';
                        })
                        ->rawColumns(['image','supplier_name','reorder','action'])
                        ->skipPaging()
                        ->setTotalRecords($dataCount)
                        ->make(true);

            return $table;
        }
    }




    public function updateForm(Request $request)
    {
        if ($request->ajax()){

            if (isset($request->id) && $request->id != null) {
                $data = Product::where([
                    'id' => $request->id
                ])->first();

                $id = $request->id;

                return view('seller.purchase_order.form-update-product-cost', compact(['data', 'id']));
            }
        }
    }

    public function showReOrderForm(Request $request)
    {
        if ($request->ajax()){

            if (isset($request->id) && $request->id != null) {
                $data = Product::where([
                    'id' => $request->id
                ])->first();

                $shipTypes = ShipType::all();
                $product_reorders = DB::select(DB::raw("SELECT *  FROM `product_reorders` WHERE product_id=$request->id"));
                $id = $request->id;
                return view('seller.purchase_order.form-update-product-reorder', compact(['data', 'id','shipTypes','product_reorders']));
            }
        }
    }

    public function updateReOrderData(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required',
            'type' => 'required',
        ]);

        if ($validator->fails()) {
            return redirect('product_cost')->with('danger','Field id is required. ');
        }
        try {

            DB::table('product_reorders')->where('product_id', $request->product_id[0])->delete();

            if (!empty($request->status)) {
                $product_id =  isset($request->product_id) ? $request->product_id : 0;
                foreach($request->status as $key => $row ) {

                    $status =  $request->status[$key];
                    $type = isset($request->type[$key]) ? $request->type[$key] : 0;
                    $quantity = isset($request->quantity[$key]) ? $request->quantity[$key] : 0;
                    $created_at = date('Y-m-d');
                    $data = array(
                        'product_id' => $product_id,
                        'status' => $status,
                         "type" => $type,
                         "quantity" => $quantity,
                         "created_at" => $created_at,
                        );


                    ProductCost::insertData('product_reorders', $data);

                }
            }

            return redirect('product_cost')->with('success','Product Reorder Successfully');

        } catch (\Throwable $th) {
            report($th);

            return redirect('product_cost')->with('danger','Something has gone wrong');
        }
    }

    public function updateProductCost(Request $request)
    {
        $editid = $request->id;
        $lowest_value = $request->input('lowest_sell_price');
        if($lowest_value !='') {
            $data = array('lowest_value' => $lowest_value);

            // Call updateData() method of Product Model
            Product::updateData($editid, $data);
            return redirect('product_cost')->with('success','Product Lowest Sell Price Updated Successfully');
        }else{
            return redirect('product_cost')->with('danger','Something has gone wrong');
        }
    }





    // *******************************************//
    // ******                                 ****//
    // *********** Cost Analysis Page *************//
    // *****                                  ****//
    // *******************************************//

    public function datatableCostAnalysis(Request $request)
    {
        if ($request->ajax()){

            if (isset($request->id) && $request->id != null) {
                $data = Product::where([
                    'id' => $request->id
                ])->first();
                $id = $request->id;
                $suppliers = Supplier::all();

                $supplier_id = $data->supplier_id;
                $exchangeRates =ExchangeRate::all();

                return view('seller.product_cost_price.form-update-product-cost', compact(['data', 'id','suppliers','supplier_id','exchangeRates']));
            }

            $start = $request->get('start', 0);
            $limit = $request->get('length', 10);
            if ($limit < 1 OR $limit > 100) {
                $limit = 100;
            }

            $search = isset($request->get('search')['value'])
                ? $request->get('search')['value']
                : null;

            $data = Product::where('seller_id', Auth::user()->id)
                ->with('preferredProductCost')
                ->searchTable($search)
                ->orderBy('id', 'desc')
                ->take($limit)
                ->skip($start)
                ->get();

            $totalData = Product::searchTable($search)->count();

            $table = Datatables::of($data)
                ->addColumn('details', function ($row) {
                    $imageContent = '<img src="'. asset('No-Image-Found.png') .'" class="w-full h-auto rounded-sm">';
                    if (!empty($row->image) && file_exists(public_path($row->image)))
                        $imageContent = '<img src="'. asset($row->image) .'" class="w-full h-auto">';

                    $cost_per_piece = $row->preferredProductCost->cost;
                    $ship_cost = $row->preferredProductCost->operation_cost;
                    $exchange_rate_name = $row->preferredProductCost->exchangeRate->name;

                    $exchange_rate = 1;
                    if($row->preferredProductCost->exchangeRate->rate)
                        $exchange_rate = $row->preferredProductCost->exchangeRate->rate;

                    $cost_per_pack = $cost_per_piece * $row->pack * $exchange_rate * $ship_cost;

                    $cost_per_piece = number_format($cost_per_piece, 3);
                    $cost_per_pack = number_format($cost_per_pack, 3);

                    $profit = $row->price - $cost_per_pack;
                    $lowest_sell_profit = $row->price - $row->lowest_value;

                    $actionBtn = '
                        <div class="flex flex-row pt-2 ">
                            <div class="w-1/4 sm:w-1/4 md:w-1/6 mb-4 md:mb-0">
                                <div class="mb-4">
                                    <span class="block mb-1"> '. $imageContent .' </span>
                                    <p class="font-bold text-blue-500">
                                        ID:<span class=""> '.$row->id.' </span>
                                    </p>
                                </div>
                            </div>
                            <div class="w-3/4 sm:w-3/4 md:w-5/6 ml-4 sm:ml-6">
                                <div class="mb-2 xl:mb-4 ">
                                    <p class="font-bold mb-2">
                                        '.$row->product_name.' <br>
                                        <span class="text-blue-500">'.$row->product_code.'</span>
                                    </p>
                                    <p class="mb-0">Price: ฿'.$row->price.'</p>
                                    <p class="mb-2">Pieces/Pack: '.$row->pack.'</p>
                                    <p class="mb-0">Preferred Supplier: '.$row->preferredProductCost->supplier->supplier_name.'</p>
                                    <p class="mb-0">Cost/Piece: '.$cost_per_piece . ' ' . $exchange_rate_name .'</p>
                                    <p class="mb-0">Cost/Pack: ฿'.$cost_per_pack.'</p>
                                    <p class="mb-2">Profit: ฿'.$profit.'</p>
                                    <p class="mb-1">Lowest Sell Price: ฿'.$row->lowest_value.'</p>
                                    <p class="mb-0">Lowest Sell Profit: ฿'.$lowest_sell_profit.'</p>
                                    <div class="flex flex-row md:hidden lg:hidden xl:hidden pt-2 mb-0">
                                        <button type="button" class="modal-open btn-action--green" x-on:click="showEditModal=true" data-id="' . $row->id . '" id="BtnUpdate">
                                            <i class="fas fa-pencil-alt"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div> ';

                    return $actionBtn;
                })
                ->addColumn('manage', function ($row) {
//                    return '
//                        <div class="hidden md:flex lg:flex xl:flex flex-row pt-2 mb-0">
//                            <button type="button" class="modal-open btn-action--green" x-on:click="showEditModal=true" data-id="' . $row->id . '" id="BtnUpdate">
//                                <i class="fas fa-pencil-alt"></i>
//                            </button>
//                        </div>
//                        ';
                })
                ->rawColumns(['manage', 'details'])
                ->skipPaging()
                ->setTotalRecords($totalData)
                ->make(true);

            return $table;
        }
    }


    public function changeProductCostPrice(Request $request)
    {
        $editid = $request->id;
        $cost_price = $request->input('cost_price');
        $ship_cost = $request->input('ship_cost');
        $cost_currency = $request->input('cost_currency');
        $lowest_value = $request->input('lowest_sell_price');
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
