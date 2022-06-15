<?php

namespace App\Http\Controllers;
use App\Http\Requests\OrderPurchase\StoreRequest;
use App\Http\Resources\ProductTypeAheadResource;
use App\Imports\BulkImportPCost;
use App\Models\Product;
use App\Models\DomesticShipper;
use App\Models\PoShipment;
use App\Models\PoShipmentDetail;
use App\Models\ProductMainStock;
use App\Models\StockLog;
use App\Models\ExchangeRate;
use App\Models\Category;
use App\Models\Supplier;
use App\Models\OrderPurchase;
use App\Models\ShipType;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Datatables;
use DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB as FacadesDB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Facades\Excel;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use App\Exports\OrderAnalysisExport;
class PurchaseOrderController extends Controller
{
    public function index()
    {
        return view('seller.purchase_order.index');
    }

    public function data(Request $request)
    {
        if ($request->ajax()) {
            if (isset($request->id) && $request->id != null) {
                $data = product::where([
                    'id' => $request->id
                ])->first();
                $id = $request->id;
                return view('elements.form-update-product', compact(['data', 'id']));
            }

            $data = product::with('getQuantity')->where('seller_id',Auth::user()->id)->orderBy('id', 'desc')->get();

            $table = Datatables::of($data)
                ->addColumn('image', function ($row) {
                    if(!empty($row->image))
                    {
                        return '<span><img src="'.asset($row->image).'" class="cutome_image" ></span>';
                    }
                })
                ->addColumn('qrCode', function ($row) {

                    return QRCode::size(100)->margin(2)->generate($row->product_code);
                })
                ->addColumn('quantity', function ($row) {
                    return '<a class="custome_quantity" href="'.url("seller/see-details/$row->id").'">'.$row->getQuantity->quantity.'</a>' ;
                })
                ->addColumn('manage', function ($row) {
                    return '<span x-on:click=" showEditModal=true"class="modal-open bg-green-500 text-white rounded px-2 py-1 mr-1 capitalize cursor-pointer" data-id="' . $row->id . '" id="BtnUpdate"><i class="fas fa-pencil-alt"></i></span><span class="bg-red-500 text-white rounded px-2 py-1 mr-1 capitalize cursor-pointer" data-id="' . $row->id . '" id="BtnDelete"><i class="fas fa-trash-alt"></i></span><span x-on:click=" showQuantityModal=true"class="modal-open bg-blue-500 text-white rounded px-2 py-1 mr-1 capitalize cursor-pointer" data-id="' . $row->id . '" id="BtnQuantity">In/Out</span><span class="modal-open bg-blue-500 text-white rounded px-2 py-1 mr-1 capitalize cursor-pointer"><a href="view-qr-code/'.$row->product_code.'" ><i class="fas fa-print"></i></a></span>';
                })
                ->addColumn('checkbox', function ($row) {
                    // return '<input type="checkbox" class="checkbox checkbox2" name="product_code[]" value="'.$row->product_code.'" style=" height: 17px; width: 17px; margin-top: 10px; padding-top: 10px !important;">';
                    return $row->id;
                })
                // ->rawColumns(['image','manage','quantity','qrCode','checkbox'])
                ->rawColumns(['image','manage','quantity','qrCode'])
                ->make(true);
            return $table;
        }
    }

    public function insert(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_name' => 'required',
            'product_code' => 'required|unique:products,product_code|string|max:255',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $data = new Product();
        $data->product_name = $request->product_name;
        $data->category_id = $request->category_id;
        $data->product_code = $request->product_code;
        $data->seller_id = Auth::User()->id;
        $data->warehouse_id =  $request->warehouse_id;
        $data->from_where = 1;
        if ($request->hasFile('image')) {
            $upload = $request->file('image');
            $file_type = $upload->getClientOriginalExtension();
            $upload_name =  time() . $upload->getClientOriginalName();
            $destinationPath = public_path('uploads/product');
            $upload->move($destinationPath, $upload_name);
            $data->image = 'uploads/product/'.$upload_name;
        }

        $result = $data->save();

        $productMainStock = new ProductMainStock();
        $productMainStock->product_id = $data->id;
        $productMainStock->quantity = 0;
        $productMainStock->save();

        QrCode::generate($data->product_code, 'qrcodes/'.$data->product_code.'.svg');

        if ($result) {
            return redirect()->back()->with('success', 'Product successfully created');
        } else {
            return redirect()->back()->with('error', 'Wrong Password');
        }
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_name' => 'required',
            'product_code' => 'required'
        ],);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $data = product::find($request->id);
        $data->product_name = $request->product_name;
        $data->category_id = $request->category_id;
        $data->product_code = $request->product_code;
        $data->warehouse_id =  $request->warehouse_id;
        if ($request->hasFile('image')) {
            $upload = $request->file('image');
            $file_type = $upload->getClientOriginalExtension();
            $upload_name =  time() . $upload->getClientOriginalName();
            $destinationPath = public_path('uploads/product');
            $upload->move($destinationPath, $upload_name);
            $data->image = 'uploads/product/'.$upload_name;
        }
        $result = $data->save();

        if ($result) {
            return redirect('/product')->with('success', 'Product successfully Updated');
        } else {
            return redirect('/product')->with('error', 'Wrong Password');
        }
    }

    public function delete(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 0,
                'message' => 'Field id is required'
            ]);
        } else {
            $product = Product::find($request->id);
            $path = "qrcodes/".$product->product_code.".svg";
            unlink($path);
            unlink($product->image);
            DB::table('products')->where([
                'id' => $request->id
            ])->delete();

            return [
                'status' => 1
            ];
        }
    }

    public function productUpdate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'check' => 'required',
            'quantity' => 'required|numeric'
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $user = Auth::user();
        if($request->quantity > 0)
        {
            $data = ProductMainStock::where('product_id',$request->id)->first();
            if($request->check == 1)
            {
                $quantity = $data->quantity + $request->quantity;
                $data->quantity = $quantity;
                $result = $data->save();

                $stockLog = new StockLog();
                $stockLog->product_id = $request->id;
                $stockLog->quantity = $request->quantity;
                $stockLog->seller_id = Auth::user()->id;
                $stockLog->date = Carbon::now('Asia/Jakarta')->format('Y-m-d H:i A');
                $stockLog->check_in_out = 1;
                $stockLog->save();

                if ($result) {
                    return redirect()->back()->with('success', 'Product quantity successfully checked in');
                } else {
                    return redirect()->back()->with('error', 'Wrong Password');
                }
            }
            else{
                if($data->quantity < $request->quantity){
                    return redirect()->back()->with('danger', 'After CheckOut this quantity. It become less then zero. Please Insert valide quantity value');
                }
                else
                {
                    $quantity = $data->quantity - $request->quantity;
                    $data->quantity = $quantity;
                    $result = $data->save();

                    $stockLog = new StockLog();
                    $stockLog->product_id = $request->id;
                    $stockLog->quantity = $request->quantity;
                    $stockLog->seller_id = Auth::user()->id;
                    $stockLog->date = Carbon::now('Asia/Jakarta')->format('Y-m-d H:i A');
                    $stockLog->check_in_out = 0;
                    $stockLog->save();
                    if ($result) {
                        return redirect()->back()->with('success', 'Product quantity successfully checked out');
                    } else {
                        return redirect()->back()->with('error', 'Wrong Password');
                    }
                }
            }
        }
        return redirect()->back()->with('danger', 'Number must be greater than zero');
    }

    public function productData(Request $request)
    {
        if ($request->ajax()) {
            if (isset($request->id) && $request->id != null) {
                $data = Product::where([
                    'id' => $request->id
                ])->first();
                $id = $request->id;

                return view('elements.form-update-quantity', compact(['data', 'id']));
            }
        }
    }

    public function seeDetails($id)
    {
        $user = Auth::user();
        $quantityLogs = StockLog::where('product_id',$id)->with('seller')->get();
        $product = Product::find($id);
        return view('seller.qunatity-log-details', compact('quantityLogs','product'));
    }

    public function deleteQuantityLog(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 0,
                'message' => 'Field id is required'
            ]);
        } else {

            // $stockLog = StockLog::find($request->id);
            // $productMainStock = ProductMainStock::where('product_id',$stockLog->product_id)->first();
            // if($stockLog->check_in_out == 1)
            // {
            //     $quantity = $productMainStock->quantity - $stockLog->quantity;
            //     $productMainStock->quantity = $quantity;
            //     $productMainStock->save();
            // }
            // else
            // {
            //     $quantity = $productMainStock->quantity + $stockLog->quantity;
            //     $productMainStock->quantity = $quantity;
            //     $productMainStock->save();
            // }
            DB::table('stock_logs')->where([
                'id' => $request->id
            ])->delete();

            return [
                'status' => 1
            ];
        }
    }

    public function updateQuantityLog(Request $request)
    {
        $validator = Validator::make($request->all(), [

            'quantity' => 'required|numeric'
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $user = Auth::user();
        if($request->quantity > 0)
        {
            $quantityLog = StockLog::find($request->id);
            // dump( $quantityLog);
            $productMainStock = ProductMainStock::where('product_id',$quantityLog->product_id)->first();
            // dump( $productMainStock);
            // exit;
            if($quantityLog->check_in_out == 1)
            {
                $quantity = $productMainStock->quantity - $quantityLog->quantity;
                $quantity = $quantity + $request->quantity ;
                $productMainStock->quantity = $quantity;
                $result = $productMainStock->save();

                $quantityLog->quantity = $request->quantity;
                $quantityLog->save();

                if ($result) {
                    return redirect()->back()->with('success', 'Product quantity successfully Updated');
                } else {
                    return redirect()->back()->with('error', 'Wrong Password');
                }
            }
            else{
                if($productMainStock->quantity < $request->quantity){
                    return redirect()->back()->with('danger', 'After CheckOut this quantity. It become less then zero. Please Insert valide quantity value');
                }
                else
                {
                    $quantity = $productMainStock->quantity + $quantityLog->quantity;
                    $quantity = $quantity - $request->quantity ;
                    $productMainStock->quantity = $quantity;
                    $result = $productMainStock->save();

                    $quantityLog->quantity = $request->quantity;
                    $quantityLog->save();

                    if ($result) {
                        return redirect()->back()->with('success', 'Product quantity successfully Updated');
                    } else {
                        return redirect()->back()->with('error', 'Wrong Password');
                    }
                }
            }
        }
        return redirect()->back()->with('danger', 'Number must be greater then zero');
    }

    public function dataQuantityLog(Request $request)
    {
        if ($request->ajax()) {

            if (isset($request->id) && $request->id != null) {
                $data = StockLog::where([
                    'id' => $request->id
                ])->first();
                $id = $request->id;

                return view('elements.form-update-quantity-log', compact(['data', 'id']));
            }
        }
    }


    public function productCost()
    {
        $categories = Category::where('seller_id',Auth::user()->id)->get();

        $data = [
            'categories' => $categories
        ];

        return view('seller.purchase_order.product_cost', $data);
    }

    public function productCostAnalysis()
    {
        $data = Product::where('seller_id',Auth::user()->id)->get();

        if(session::has('product_data')) {
            $data = session::forget('product_data');
        }
        return view('seller.product_cost_price.index', compact('data'));
    }


    public function createProductCost()
    {
        Session::put('itemArray', []);

        $sellerId = Auth::user()->id;

        $products = Product::where('seller_id', $sellerId)->get();
        $suppliers = Supplier::where('seller_id', $sellerId)->get();

        $data = [
            'products' => ProductTypeAheadResource::collection($products),
            'suppliers' => $suppliers,
            'exchangeRates' => ExchangeRate::where('seller_id', $sellerId)->orderBy('name', 'asc')->get()
        ];

        return view('seller.purchase_order.create-product-cost', $data);
    }

    


    
        /**
     * Handle the `product stock report` datatable
     * Serverside Datatable
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     *
     */
    public function POProductAnalysisData(Request $request)
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
                'quantity',
                'total_incoming',
                'alert_stock',
                'alert_stock',
                'supplier_name',
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
                'supplier_id' => $supplierId,
                'category_id' => $categoryId
            ];

            $data = Product::reportStockTable($sellerId, $reportStatus, $otherReportParams);
            $dataCount = Product::reportStockTableCount($sellerId, $reportStatus, $otherReportParams);

            $table = Datatables::of($data)
                        ->addColumn('checkbox', function ($row) {
                            return $row->id; 
                        })
                        ->addColumn('image', function ($row) {
                            return '<img src="'. product_image_url($row->image) .'" class="w-28 h-auto">';
                        })
                        ->addColumn('quantity', function ($row) {
                            return '<a href="'. route('seller quantity details', [ 'id' => $row->id ]) .'" class="text-gray-800 underline">'
                                        . number_format($row->quantity) .
                                    '</a>' ;
                        })
                        ->addColumn('incoming', function ($row) {
                                return number_format($row->total_incoming);

                        })

                        ->addColumn('reorder_qty', function ($row) {
                            if (!empty($row->reorder_status)) {
                                $Arr_reorder_status = explode(",",$row->reorder_status);
                                $Arr_reorder_ship_type = explode(",",$row->reorder_shiptype);
                                $Arr_reorder_qty = explode(",",$row->reorder_qty);
                                //print_r();
                                $list ='';
                                foreach($Arr_reorder_status as $index=>$status_name){
                                    $status = ucwords(str_replace("_", " ", $status_name));
                                    $list .= '<p class="mb-2">'.$status.' '.$Arr_reorder_ship_type[$index].' ('.$Arr_reorder_qty[$index].')</p>';
                                    
                                }
                                return $list ;
                            }
                        })

                        ->addColumn('supplier_name', function ($row) {
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
                       
                        ->rawColumns(['checkbox','image', 'quantity', 'incoming', 'reorder_qty','supplier_name','report_status'])
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
                $suppliers = Supplier::all();
                $supplier_id = $data->supplier_id;
                return view('seller.purchase_order.form-update-order-analysis', compact(['data', 'id','suppliers','supplier_id']));
            }
        }
    }

    public function updateReorderStock(Request $request)
    {
        $editid = $request->id;
        $low_stock_reorder = $request->input('low_stock_reorder');
        $out_of_stock_reorder = $request->input('out_of_stock_reorder');
        $supplier_id = $request->input('supplier_id');

        if($low_stock_reorder !='' || $out_of_stock_reorder != '' || $supplier_id != '') {
            $data = array('low_stock_reorder' => $low_stock_reorder, "out_of_stock_reorder" => $out_of_stock_reorder, "supplier_id" => $supplier_id);

            // Call updateData() method of Product Model
            Product::updateData($editid, $data);
            return redirect('order_analysis')->with('success','Product Rorder Stock Updated Successfully');
        }else{
            return redirect('order_analysis')->with('danger','Something has gone wrong');
        }
    }



    public function poSettings(Request $request)
    {
        $data_agent_cargo = DB::table('agent_cargo_name')->get();
         $shipTypes = ShipType::all();

        $data = [
            'data_agent_cargo' =>$data_agent_cargo,
            'title' => 'China Cargo'
        ];

        return view('seller.purchase_order.settings.china_cargo', $data);
    }



    public function createChinaCargo()
    {

         $shipTypes = ShipType::all();

        $data = [
            'shipTypes' => $shipTypes,
        ];

        return view('seller.purchase_order.settings.create_china_cargo', $data);
    }



    /**
     * Store `china_cargo` data
     */
    public function storeChinaCargo(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Field id is required. '
            ], 500);
        }
        try {

            $created_at = date('Y-m-d');
            if (!empty($request)) {
                    $name = isset($request->name) ? $request->name : 0;

                    $data = array(
                        'name' => $name,
                         "created_at" => $created_at,
                    );

                    $insertedId = OrderPurchase::insertData('agent_cargo_name', $data);


                    if (!empty($request->shipping_mark)) {
                        foreach($request->shipping_mark as $key => $row ) {

                            $data_sm = array(
                                'agent_cargo_id' => isset($insertedId) ? $insertedId : 0,
                                'shipping_mark' => isset($request->shipping_mark[$key]) ? $request->shipping_mark[$key] : 0,
                                'ship_type_id' => isset($request->ship_type_id[$key]) ? $request->ship_type_id[$key] : 0,
                                 "created_at" => $created_at,
                            );
                            OrderPurchase::insertData('agent_cargo_mark', $data_sm);
                        }
                    }


                    if (!empty($request->address)) {
                        foreach($request->address as $key => $row ) {

                            $data_sw = array(
                                'agent_cargo_id' => isset($insertedId) ? $insertedId : 0,
                                'location' => isset($request->location[$key]) ? $request->location[$key] : 0,
                                'address' => isset($request->address[$key]) ? $request->address[$key] : 0,
                                 "created_at" => $created_at,
                            );
                            OrderPurchase::insertData('agent_cargo_warehouse', $data_sw);
                        }
                    }



            }

           // http_response_code(500);
           // dd($request->address); die;
            return response()->json([
                'message' => 'Dats Save Successfully.'
           ]);



        } catch (\Throwable $th) {
            report($th);
            return response()->json([
                'message' => 'Something has gone wrong'
           ]);

        }
    }



    public function editFormChinaCargo($id)
    {

        $data_agent_cargo = DB::table('agent_cargo_name')->where('id', $id)->first();

        if(!empty($data_agent_cargo)){
            $agent_cargo_id = $data_agent_cargo->id;
            $agent_cargo_marks = DB::table('agent_cargo_mark')->where('agent_cargo_id', $id)->get();
            $agent_cargo_warehouses = DB::table('agent_cargo_warehouse')->where('agent_cargo_id', $id)->get();
        }
         $shipTypes = ShipType::all();

        $data = [
            'id' =>$id,
            'data_agent_cargo' =>$data_agent_cargo,
            'shipTypes' => $shipTypes,
            'agent_cargo_marks' => $agent_cargo_marks,
            'agent_cargo_warehouses' => $agent_cargo_warehouses,
        ];

        return view('seller.purchase_order.settings.edit_china_cargo', $data);
    }



    /**
     * Update `china_cargo` data
     */
    public function updateChinaCargo(Request $request)
    {

        $id = $request->id;
        $validator = Validator::make($request->all(), [
            'name' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Field id is required. '
            ], 500);
        }
        try {

            $created_at = date('Y-m-d');
            if (!empty($request)) {
                    $name = isset($request->name) ? $request->name : 0;
                    $data = array(
                        'name' => $name,
                         "created_at" => $created_at,
                    );

                    $updateId  = DB::table('agent_cargo_name')->where('id', $id)->update(['name' => $name]);
                   // http_response_code(500);
                   // dd($updateId);
                    //DB::table('agent_cargo_name')->where('id',$id)->delete();
                    //$insertedId = OrderPurchase::insertData('agent_cargo_name', $data);


                        DB::table('agent_cargo_mark')->where('agent_cargo_id',$id)->delete();

                        if (!empty($request->shipping_mark)) {
                            foreach($request->shipping_mark as $key => $row ) {

                                $data_sm = array(
                                    'agent_cargo_id' => $id ,
                                    'shipping_mark' => isset($request->shipping_mark[$key]) ? $request->shipping_mark[$key] : 0,
                                    'ship_type_id' => isset($request->ship_type_id[$key]) ? $request->ship_type_id[$key] : 0,
                                    "created_at" => $created_at,
                                );

                                OrderPurchase::insertData('agent_cargo_mark', $data_sm);
                            }
                        }

                        DB::table('agent_cargo_warehouse')->where('agent_cargo_id',$id)->delete();
                        if (!empty($request->address)) {
                            foreach($request->address as $key => $row ) {

                                $data_sw = array(
                                    'agent_cargo_id' => $id,
                                    'location' => isset($request->location[$key]) ? $request->location[$key] : 0,
                                    'address' => isset($request->address[$key]) ? $request->address[$key] : 0,
                                    "created_at" => $created_at,
                                );

                                OrderPurchase::insertData('agent_cargo_warehouse', $data_sw);
                            }
                        }




            }

           // http_response_code(500);
           // dd($request->address); die;
            return response()->json([
                'message' => 'Dats Save Successfully.'.$updateId
           ]);



        } catch (\Throwable $th) {
            report($th);
            return response()->json([
                'message' => 'Something has gone wrong'
           ]);

        }
    }




        /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public static function deleteChinaCargo($id)
    {


        $agent_cargo_name = DB::table('agent_cargo_name')->where('id',$id)->delete();
        if($agent_cargo_name){
           DB::table('agent_cargo_mark')->where('agent_cargo_id',$id)->delete();
           DB::table('agent_cargo_warehouse')->where('agent_cargo_id',$id)->delete();
           return redirect()->back()->with('success','Channel has been deleted successfully');
        }
        else{
            return redirect()->back()->with('danger','Something went wrong');
        }
    }








}
