<?php

namespace App\Http\Controllers;

use App\Http\Requests\InOut\StoreRequest;
use App\Http\Requests\InOutProductHistory\DeleteRequest;
use App\Http\Requests\InOutProductHistory\UpdateRequest;
use App\Http\Resources\ProductTypeAheadResource;
use App\Jobs\InventoryQtySync;
use App\Models\ActivityLog;
use DB;
use URL;
use App\Models\Product;
use App\Models\OrderPurchaseDetail;
use App\Models\ProductMainStock;
use App\Models\ProductPrice;
use App\Models\StockLog;
use App\Models\User;
use App\Models\Supplier;
use Barryvdh\DomPDF\Facade as PDF;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Phattarachai\LineNotify\Facade\Line;
use Illuminate\Support\Facades\Storage;

class QrCodeController extends Controller
{
    public function viewQrCode($id)
    {
        return view('qrCode.view-qr-code', compact('id'));
    }

    public function generateQrCode()
    {

        $product = Product::where('from_where', 2)->get();
        return view('qrCode.generate-qr-code', compact('product'));
    }

    public function addProductCode(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_name' => 'required',
            'product_code' => 'required|unique:products,product_code|string|max:255',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }
        $product = new Product();
        $product->product_name = $request->product_name;
        $product->product_code = $request->product_code;
        $product->seller_id = Auth::user()->id;
        $product->from_where = 2;
        $product->save();

        $productMainStock = new ProductMainStock();
        $productMainStock->product_id = $product->id;
        $productMainStock->quantity = 0;
        $productMainStock->save();

        QrCode::generate($product->product_code, 'qrcodes/'.$product->product_code.'.svg');
        return redirect('/generate-qr-code')->with('success', 'Product Code added Successfully');
    }

    public function generateQrCodePdf(Request $request)
    {

      if(isset($request->product_code))
      {
        //   dd($request->product_code);
        $products = Product::whereIn('product_code',$request->product_code)->get();

        $customPaper = array(0,0,60.00,50.80);
            // $pdf = PDF::loadView('pdf.retourlabel', compact('retour','barcode'))->setPaper($customPaper, 'landscape');
        $width= '600';
        $height = '1200';

        $pdf = PDF::loadView('qrCode.generate-qr-code-pdf',compact('products'))
        ->setOptions(['defaultFont' => 'Courier'])
        ->setOptions(['isHtml5ParserEnabled' => true, 'isRemoteEnabled' => true])
        //->setPaper('A3', 'portrait');
        ->setPaper(array(0,0,$width,$height));

            //         $paper_size = array(0,0,360,360);
            // $dompdf->set_paper($paper_size);

        $time = strtotime("tpday");
        return $pdf->download('qr-code-'.$time.'.pdf');
      }

      return redirect()->back()->with('danger','Please select any product before print');


    }

    public function generateQrCodePdf1(Request $request)
    {
        if (isset($request->product_code) && count($request->product_code) > 0) {
            $products = Product::whereIn('product_code',$request->product_code)->get();
            $products->map(function($product) {
                // if (!file_exists(public_path('qrcodes/' . $product->product_code . '.svg'))) {
                //     QrCode::generate($product->product_code, public_path('qrcodes/' . $product->product_code . '.svg'));
                // }
            });

            return view('qrCode.get_qr_code', compact('products'));
        }

        return redirect()->back()->with('danger','Please select any product before print');
    }

    /**
     * Show product stock adjustment page
     *
     * @return \Illuminate\View\View
     */
    public function inOutWithQrCode()
    {
        Session::put('itemArray', []);

        $products = Product::where('seller_id', Auth::user()->id)->get();

        $data = [
            'products' => ProductTypeAheadResource::collection($products)
        ];

        return view('qrCode.in-out', $data);
    }

    /**
     * Get product by `product_code`
     *
     * @param \Illuminate\Http\Request
     * @return \Illuminate\Http\Response
     */
    public function getQrCodeProduct(Request $request)
    {
        $statusNotFound = 1;
        $statusFound = 3;

        if(!empty($request->product_code)){
            $productCode = $request->product_code;

            $product = Product::with('getQuantity')
                            ->where('seller_id',Auth::user()->id)
                            ->where(function($query) use ($productCode) {
                                $query->where('product_code', $productCode)
                                    ->orWhere('product_name', $productCode);
                                })
                            ->first();

            $image_path = '';
            if(!is_null($product)):
                if(Storage::disk('s3')->exists($product->image)) {
                    $image_path = Storage::disk('s3')->url($product->image);
                }
            endif;

            if (empty($product)) {
                return response()->json([
                    'status' => $statusNotFound
                ]);
            }

            return response()->json([
                'status' => $statusFound,
                'product' => $product,
                'product_image_url' =>$image_path
            ]);
        }
    }

    /**
     * Store to `stock_logs` table
     *
     * @param \App\Http\Requests\InOut\StoreRequest $request
     * @return \Illuminate\Http\Response
     */
    public function updateInOut(StoreRequest $request)
    {
        if(isset($request->product_id) && isset($request->adjust_stock))
        {
            foreach($request->product_id as $key=>$item)
            {
                $product[$key][0] = $item;
            }

            foreach($request->adjust_stock as $key=>$item)
            {
                $product[$key][1] = $item;
            }

            if($request->check == 1)
            {
                foreach($product as $item)
                {
                    $stockLog = new StockLog();
                    $stockLog->product_id = $item[0];
                    $stockLog->quantity = $item[1];
                    if (Auth::user()->role == 'staff')
                        $stockLog->staff_id = Auth::user()->staff_id;
                    $stockLog->seller_id = Auth::user()->id;
                    $stockLog->date = Carbon::now(config('app.timezone'))->format('Y-m-d H:i');
                    $stockLog->check_in_out = 1;
                    $stockLog->is_defect = 0;
                    if($stockLog->save()){
                        $dodoProduct = Product::find($item[0]);
                        InventoryQtySync::dispatch($dodoProduct);

                        if($dodoProduct->child_products):
                            $child_sku = explode(",", $dodoProduct->child_products);
                            foreach ($child_sku as $child):
                                $dodoChildProduct = Product::query()
                                    ->where('product_code', trim($child))
                                    ->with('getQuantity')
                                    ->firstOrFail();
                                InventoryQtySync::dispatch($dodoChildProduct, $dodoProduct->getQuantity->quantity)
                                    ->delay(Carbon::now()->addSeconds(1));
                            endforeach;
                        endif;
                    }
                }
            }
            else{
                foreach($product as $item)
                {
                    $stockLog = new StockLog();
                    $stockLog->product_id = $item[0];
                    $stockLog->quantity = $item[1];
                    if (Auth::user()->role == 'staff')
                        $stockLog->staff_id = Auth::user()->staff_id;
                    $stockLog->seller_id = Auth::user()->id;
                    $stockLog->date = Carbon::now(config('app.timezone'))->format('Y-m-d H:i');
                    $stockLog->check_in_out = 0;
                    $stockLog->is_defect = 0;
                    if($stockLog->save()){
                        $dodoProduct = Product::find($item[0]);
                        InventoryQtySync::dispatch($dodoProduct);

                        if($dodoProduct->child_products):
                            $child_sku = explode(",", $dodoProduct->child_products);
                            foreach ($child_sku as $child):
                                $dodoChildProduct = Product::query()
                                    ->where('product_code', trim($child))
                                    ->with('getQuantity')
                                    ->firstOrFail();
                                InventoryQtySync::dispatch($dodoChildProduct, $dodoProduct->getQuantity->quantity)
                                    ->delay(Carbon::now()->addSeconds(1));
                            endforeach;
                        endif;
                    }
                }
            }

             $data = Product::with('getQuantity')->where('id',$request->product_id)->orderBy('id', 'desc')->get();

            $adjust_stock = $request->adjust_stock[0];
            $row = $data[0];
            if(isset($row->image)):
                $image = Storage::disk('s3')->url($row->image);
            else:
                $image = Storage::disk('s3')->url($row->image_url);
            endif;

            $image_url = URL::asset($image);
            $current_stock = $row->getQuantity->quantity;
            $previous_stock = $row->getQuantity->quantity - $adjust_stock;
            $alert_stock = $row->alert_stock;
            $previous_status = $this->statusFinder($previous_stock, $alert_stock);
            $current_status = $this->statusFinder($current_stock, $alert_stock);

            if(($previous_status != $current_status) || $current_stock=='0'){
                $product_name = $row->product_name;
                $product_code = $row->product_code;
                $message = $product_name."(".$product_code.")";

                $orderPurchaseDetailTable = (new OrderPurchaseDetail())->getTable();

                $incoimg_products = DB::table('order_purchase_details')
                ->join('order_purchases', 'order_purchases.id', '=', 'order_purchase_details.order_purchase_id')
                        ->where('order_purchase_details.product_id', '=', $request->product_id)
                        ->where('order_purchases.seller_id',Auth::user()->id)
                        ->where('order_purchases.status', '=', 'open')
                        ->select('order_purchases.*', 'order_purchase_details.quantity', 'order_purchase_details.order_purchase_id')
                        ->get();


                $message =  view('qrCode.line_notify',compact('incoimg_products','current_status','current_stock','product_name','product_code'));
                Line::imageUrl($image_url)->send($message);
            }
            return true;
        }

        return null;
    }

    public function statusFinder( $quantity, $alert_stock){
        if($alert_stock != '' && $quantity <= '0')
        {
            return 'OUT OF STOCK';
        }
        elseif($quantity > 0 && $quantity <= $alert_stock )
        {
            return 'LOW STOCK';
        }
        elseif($alert_stock != '' && $quantity > $alert_stock )
        {
            return 'OVERSTOCK';
        }
        elseif ($alert_stock == '')
        {
            return 'N/A';
        }
    }

    public function resetQrCodeProduct(){
        if(Session::has('itemArray')){
            Session::forget('itemArray');
            $data = [];
            Session::put('itemArray',$data);
        }
    }

    public function deleteSessionProduct(Request $request){
        if(Session::has('itemArray')){
           $sessionData = Session::get('itemArray');
            foreach($sessionData as $key=>$item)
                {
                    if(strtolower($item) == strtolower($request->product_code))
                    {
                        unset($sessionData[$key]);
                    }
                }
            Session::forget('itemArray');
            Session::put('itemArray',$sessionData);
            return true;
        }
    }

    public function getAutocomplete(Request $request){

        $search = $request->search;

        if($search == ''){
           $autocomplate = Product::orderby('id','asc')->select('id','product_code')->where('seller_id',Auth::user()->id)->limit(5)->get();
        }else{
        //    $autocomplate = Product::orderby('id','asc')->where('seller_id',Auth::user()->id)->where('product_code', 'like', '%' .$search . '%')->Orwhere('product_name', 'like', '%' .$search . '%')->get();
           $autocomplate = Product::orderby('id','asc')->where('seller_id',Auth::user()->id)
                            ->where(function($query) use ($search){
                                $query->where('product_code', 'like', '%' .$search . '%')
                                ->Orwhere('product_name', 'like', '%' .$search . '%');
                                })
                                ->limit(5)
                                ->get();
        }

        // dd($autocomplate);
        $response = array();
        foreach($autocomplate as $autocomplate){
           $response[] = array("value"=>$autocomplate->product_code,"label"=>$autocomplate->product_name." (".$autocomplate->product_code.")");
        }

        echo json_encode($response);
        exit;
    }

    public function getQrCodeProductForOrderPurchase(Request $request)
    {
        if($request->from == 1){
            if(!empty($request->shop_id) && isset($request->shop_id))
            {
                $getShopId = 'shop_id'.$request->shop_id;
            }
            else{
                $getShopId = '';
            }
            if(!empty($request->product_code)){
                $getData = $this->get_string_between($request->product_code,'(',')');

                // $product = Product::with('getQuantity')->where('product_code',$request->product_code)->Orwhere('product_name',$request->product_code)->first();
                $product = Product::with('getQuantity')->where('seller_id',Auth::user()->id)
                ->where(function($query) use ($getData){
                    $query->where('product_code',$getData)
                    ->Orwhere('product_name',$getData);
                    })
                    ->first();
                    if(!empty($product)){
                        if($request->shop_id)
                        {
                            $price = ProductPrice::where('seller_id',Auth::user()->id)->where('product_id',$product->id)->where('shop_id',$request->shop_id)->first();
                            if($price)
                            {
                                $product->shop_price = $price->price;
                            }
                            else
                            {
                                $product->shop_price = $product->price;
                            }
                        }
                        else
                        {
                            $product->shop_price = $product->price;
                        }



                    }

                if(empty($product))
                {
                    return null;
                }
                $sessionData = Session::get('itemArray');
                if(!empty($sessionData))
                {
                    foreach($sessionData as $item)
                    {
                        // $product2 = Product::with('getQuantity')->where('product_code',$request->product_code)->Orwhere('product_name',$request->product_code)->first();
                        $product2 = Product::with('getQuantity')->where('seller_id',Auth::user()->id)
                        ->where(function($query) use ($getData){
                            $query->where('product_code',$getData)
                            ->Orwhere('product_name',$getData);
                            })
                        ->first();
                        if(strtolower($item) == strtolower($product2->product_code.$getShopId))
                        {
                            return null;
                        }
                    }
                }
                Session::forget('itemArray');
                array_push($sessionData,$product->product_code.$getShopId);
                Session::put('itemArray',$sessionData);
                // dd($sessionData);
                return response()->json($product);
            }
        }
        else {

            if (!empty($request->product_code)) {
                $product = Product::with('getQuantity')
                ->with('getIncoming')
                ->with('productCostDetails')
                ->where('seller_id', Auth::user()->id)
                ->where('product_code', $request->product_code)
                ->first();

                abort_if(!$product, 404, 'Product not found');

                if ($product->id) {
                    $product_costs = DB::select(DB::raw("
                    SELECT PC.*,E.name as currency_name,S.supplier_name
                    FROM `product_costs` PC
                    LEFT JOIN `suppliers` S ON S.id=PC.supplier_id
                    LEFT JOIN `exchange_rate` E ON E.id=PC.exchange_rate_id
                    WHERE PC.product_id=$product->id AND PC.default_supplier=1
                    "));
                    $list ='';
                    foreach($product_costs as $product_cost){
                        $product->default_currency = '<p class="mb-2"><strong>Default:</strong>'.$product_cost->cost.' '.$product_cost->currency_name.'</p>';
                        $product->supplier_name = '<p class="mb-2">'.$product_cost->supplier_name.'</p>';
                    }
                }



                $reorders = DB::select(DB::raw("
                                SELECT PR.*,ST.name as ship_type
                                FROM `product_reorders` PR
                                LEFT JOIN `ship_types` ST ON ST.id=PR.type
                                WHERE PR.product_id=$product->id
                                "));
                                $list ='';
                                foreach($reorders as $reorder){
                                   $status = ucwords(str_replace("_", " ", $reorder->status));
                                    $list .= '<p class="mb-2">'.$status.' '.$reorder->ship_type.' ('.$reorder->quantity.')</p>';
                                }
                $product->reorder =  $list ;




                $incoming = DB::select(DB::raw("
                                SELECT SUM(quantity) as total_incoming
                                FROM `order_purchase_details` PD
                                WHERE PD.product_id=$product->id
                                "));

                $product->total_incoming =  $incoming[0]->total_incoming ? $incoming[0]->total_incoming :0 ;
                $current_stock = $product->getQuantity->quantity;
                $product->current_status = $this->statusFinder($current_stock, $product->alert_stock);




                return response()->json([
                    'message' => 'success',
                    'data' => $product
                ]);
            }
        }
    }






    public function getQrCodeProductForCost(Request $request)
    {
        if($request->from == 1){
            if(!empty($request->shop_id) && isset($request->shop_id))
            {
                $getShopId = 'shop_id'.$request->shop_id;
            }
            else{
                $getShopId = '';
            }
            if(!empty($request->product_code)){
                $getData = $this->get_string_between($request->product_code,'(',')');

                // $product = Product::with('getQuantity')->where('product_code',$request->product_code)->Orwhere('product_name',$request->product_code)->first();
                $product = Product::with('getQuantity')->where('seller_id',Auth::user()->id)
                ->where(function($query) use ($getData){
                    $query->where('product_code',$getData)
                    ->Orwhere('product_name',$getData);
                    })
                    ->first();
                    if(!empty($product)){
                        if($request->shop_id)
                        {
                            $price = ProductPrice::where('seller_id',Auth::user()->id)->where('product_id',$product->id)->where('shop_id',$request->shop_id)->first();
                            if($price)
                            {
                                $product->shop_price = $price->price;
                            }
                            else
                            {
                                $product->shop_price = $product->price;
                            }
                        }
                        else
                        {
                            $product->shop_price = $product->price;
                        }
                    }

                if(empty($product))
                {
                    return null;
                }
                $sessionData = Session::get('itemArray');
                if(!empty($sessionData))
                {
                    foreach($sessionData as $item)
                    {
                        // $product2 = Product::with('getQuantity')->where('product_code',$request->product_code)->Orwhere('product_name',$request->product_code)->first();
                        $product2 = Product::with('getQuantity')->where('seller_id',Auth::user()->id)
                        ->where(function($query) use ($getData){
                            $query->where('product_code',$getData)
                            ->Orwhere('product_name',$getData);
                            })
                        ->first();
                        if(strtolower($item) == strtolower($product2->product_code.$getShopId))
                        {
                            return null;
                        }
                    }
                }
                Session::forget('itemArray');
                array_push($sessionData,$product->product_code.$getShopId);
                Session::put('itemArray',$sessionData);
                // dd($sessionData);
                return response()->json($product);
            }
        }
        else {

            if (!empty($request->product_code)) {
                $product = Product::with('getQuantity')
                                ->where('seller_id', Auth::user()->id)
                                ->where('product_code', $request->product_code)
                                ->first();

                abort_if(!$product, 404, 'Product not found');

                return response()->json([
                    'message' => 'success',
                    'data' => $product
                ]);
            }
        }
    }

    public function deleteSessionProduct2(Request $request){

        if(!empty($request->shop_id) && isset($request->shop_id))
        {
            $getShopId = 'shop_id'.$request->shop_id;
        }
        else{
            $getShopId = '';
        }
        if(Session::has('itemArray')){
           $sessionData = Session::get('itemArray');
            foreach($sessionData as $key=>$item)
                {
                    if(strtolower($item) == strtolower($request->product_code.$getShopId))
                    {
                        unset($sessionData[$key]);
                    }
                }
            Session::forget('itemArray');
            Session::put('itemArray',$sessionData);
            return true;
        }
    }

    public function get_qr_code(){
        return view('qrCode.get_qr_code');
    }

    function get_string_between($string, $start, $end){
        $string = ' ' . $string;
        $ini = strpos($string, $start);
        if ($ini == 0) return '';
        $ini += strlen($start);
        $len = strpos($string, $end, $ini) - $ini;
        return substr($string, $ini, $len);
    }

    /**
     * Product in/out history server side datatable
     *
     * @param \Illuminate\Http\Request $request
     * @param \Illuminate\Http\Response
     */
    public function inOutDataTable(Request $request)
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
            'id', 'id', 'product_name', 'product_code', 'check_in_out', 'quantity', 'seller_name', 'date'
        ];

        $orderColumnName = isset($availableColumnsOrder[$orderColumnIndex])
                            ? $availableColumnsOrder[$orderColumnIndex]
                            : $availableColumnsOrder[6];

        $fields = StockLog::where('is_defect', 0)
//                    ->with('seller', 'staff')
                    ->with('product')
                    ->productNameAsColumn()
                    ->productCodeAsColumn()
                    ->sellerNameAsColumn()
                    ->searchProductHistoryTable($search)
                    ->orderBy($orderColumnName, $orderDir)
                    ->take($limit)
                    ->skip($start)
                    ->get();

        if (!empty($fields)) {
            foreach ($fields as $field) {
                $row = [];

                $row[] = $field->id;
                $row[] = $field->id;
                $row[] = $field->product->product_name;
                $row[] = $field->product->product_code;
                $row[] = $field->str_in_out;
                $row[] = number_format($field->quantity);
                $row[] = !empty($field->staff) ? $field->staff->name : $field->seller->name;
                $row[] = $field->date->format('Y-m-d H:i');
                $row[] = '
                    <button
                        type="button"
                        class="btn-action--green"
                        title="Edit"
                        data-id="'. $field->id .'"
                        onClick="editHistory(this)">
                        <i class="fas fa-pencil-alt"></i>
                    </button>
                    <button
                        type="button"
                        class="btn-action--red"
                        title="Delete"
                        data-id="'. $field->id .'"
                        onClick="deleteStock(this)">
                        <i class="fas fa-trash"></i>
                    </button>
                ';

                $data[] = $row;
            }
        }

        $count_total = StockLog::where('is_defect', 0)->whereHas('product')->count();
        $count_total_search = StockLog::where('is_defect', 0)->searchProductHistoryTable($search)->count();

        $response = [
            'draw' => $request->get('draw'),
            'recordsTotal' => $count_total,
            'recordsFiltered' => $count_total_search,
            'data' => $data
        ];

        return response()->json($response);
    }

    /**
     * Show in/out history product table
     *
     * @return \Illuminate\View\View
     */
    public function inOutHistory()
    {
        return view('qrCode.in-out-history');
    }

    /**
     * Get single in/out history data
     *
     * @param int $historyId
     * @param \Illuminate\Http\Response
     */
    public function inOutHistoryDetail($id)
    {
        $history = StockLog::where('id', $id)
                    ->with('product')
                    ->with('seller')
                    ->with('staff')
                    ->first();

        abort_if(!$history, 404, 'Data not found');

        $user = [
            'id' => $history->seller->id ?? 0,
            'name' => $history->seller->name ?? ''
        ];

        if (!empty($history->staff)) {
            $user = [
                'id' => $history->staff->id ?? 0,
                'name' => $history->staff->name ?? ''
            ];
        }

        return response()->json([
            'message' => 'Success',
            'data' => [
                'id' => $history->id,
                'quantity' => $history->quantity,
                'date' => $history->date,
                'check_in_out' => $history->check_in_out,
                'str_in_out' => $history->str_in_out,
                'product' => [
                    'id' => $history->product->id,
                    'name' => $history->product->product_name,
                    'code' => $history->product->product_code
                ],
                'user' => $user
            ]
        ]);
    }

    /**
     * Update `stock_logs` data
     *
     * @param \App\Http\Requests\InOutProductHistory\UpdateRequest $request
     * @return \Illuminate\Http\Response
     */
    public function inOutHistoryUpdate(UpdateRequest $request)
    {
        try {
            $stockLog = StockLog::where('id', $request->id)->first();
            $stockLog->date = $request->datetime;
            $stockLog->quantity = $request->quantity;
            $stockLog->save();

            return response()->json([ 'message' => 'Data updated' ]);

        } catch (\Throwable $th) {
            report($th);

            return response()->json([ 'message' => $th->getMessage() ], 500);
        }
    }

    /**
     * Delete `stock_logs` data
     *
     * @param \App\Http\Requests\InOutProductHistory\DeleteRequest $request
     * @return \Illuminate\Http\Response
     */
    public function inOutHistoryDelete(DeleteRequest $request)
    {
        try {
            StockLog::destroy($request->id);

            return response()->json([ 'message' => 'Data deleted' ]);

        } catch (\Throwable $th) {
            report($th);

            return response()->json([ 'message' => $th->getMessage() ], 500);
        }
    }
}
