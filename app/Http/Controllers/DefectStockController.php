<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductMainStock;
use App\Models\StockLog;
use App\Models\StockProductDeffectImage;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Datatables;
use DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\File\File;

class DefectStockController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('qrCode.defect_stock.index');
    }

    public function data(Request $request)
    {
        if ($request->ajax()){

            if (isset($request->id) && $request->id != null) {
                $data = StockLog::findOrFail($request->id);
                $defectImages = StockProductDeffectImage::where('stock_id', $data->id)->get();

                return view('elements.form-update-defect-product', compact(['data', 'defectImages']));
            }


            $sellerId = Auth::user()->id;

            $stockLogsTable = (new StockLog())->getTable();
            $productsTable = (new Product())->getTable();

            $start = $request->get('start', 0);
            $limit = $request->get('length', 10);
            $search = isset($request->get('search')['value'])
                    ? $request->get('search')['value']
                    : null;

            $orderColumnIndex = isset($request->get('order')[0]['column'])
                                ? $request->get('order')[0]['column']
                                : 0;

            $orderDir = isset($request->get('order')[0]['dir'])
                        ? $request->get('order')[0]['dir']
                        : 'desc';

            $availableColumnsOrder = [
                'product_id', 'deffect_status'
            ];

            $orderColumnName = isset($availableColumnsOrder[$orderColumnIndex])
                            ? $availableColumnsOrder[$orderColumnIndex]
                            : $availableColumnsOrder[0];

            $stockLogs = StockLog::selectRaw("{$stockLogsTable}.*, {$productsTable}.product_name, {$productsTable}.product_code, {$productsTable}.image AS product_image")
                    ->where('is_defect', 1)
                    ->where("{$stockLogsTable}.seller_id", $sellerId)
                    ->with('seller')
                    ->joinedDefectStockDatatable()
                    ->searchDefectStockDatatable($search)
                    ->orderBy($orderColumnName, $orderDir)
                    ->skip($start)
                    ->take($limit)
                    ->get();

            $stockLogsCount = StockLog::where('is_defect', 1)
                                ->where("{$stockLogsTable}.seller_id", $sellerId)
                                ->joinedDefectStockDatatable()
                                ->count();

            $table = Datatables::of($stockLogs)
                    ->addColumn('details', function($row) {
                        $productImage = asset('No-Image-Found.png');

                        if (!empty($row->product_image) && file_exists(public_path($row->product_image))) {
                            $productImage = asset($row->product_image);
                        }

                        $detailsContent = '
                            <div class="flex flex-row gap-4">
                                <div class="w-1/2 sm:w-1/3 md:w-1/4 lg:w-1/5 xl:w-1/6">
                                    <div class="mb-3">
                                        <img src="' . $productImage . '" class="w-full h-auto">
                                    </div>
                                    <div>
                                        <span class="text-blue-500 font-bold">
                                            ID: ' . $row->product_id . '
                                        </span>
                                    </div>
                                </div>
                                <div class="w-1/2 sm:w-2/3 md:w-3/4 lg:w-4/5 xl:w-5/6">
                                    <div class="grid grid-col-1 lg:grid-cols-3 lg:gap-y-1 lg:gap-x-4">
                                        <div class="lg:col-span-2">
                                            <span class="font-bold">
                                                ' . $row->product_name . '
                                            </span><br>
                                            <span class="text-blue-500 font-bold">
                                                ' . $row->product_code . '
                                            </span>
                                        </div>
                                        <div class="lg:col-span-1">
                                            <label class="text-gray-700 font-bold">Quantity:</label> ' . $row->quantity . '
                                        </div>
                                        <div class="lg:col-span-2">
                                            <label class="text-gray-700 font-bold">Problem:</label> ' . nl2br(e($row->deffect_note)) . '
                                            <p class="text-gray-700 font-bold BtnResult" x-on:click="showResultModal=true" data-id="' . $row->id . '" id="BtnResult">Result</p>
                                        </div>
                                        <div class="lg:col-span-1">
                                            <label class="text-gray-700 font-bold">Status:</label> <span class="text-blue-500 font-bold">' . ucfirst($row->deffect_status) . '</span>
                                        </div>
                                        <div class="lg:col-span-2">
                                            <label class="text-gray-700 font-bold">Created At:</label> ' . $row->created_at->format('d M Y H:i') . '
                                        </div>
                                        <div class="lg:col-span-1">
                                            <label class="text-gray-700 font-bold">Added By:</label> ' .  $row->seller->name  . '
                                        </div>
                                    </div>
                                </div>
                            </div>
                        ';

                        return $detailsContent;
                    })
                    ->addColumn('actions', function ($row) {
                        return '
                            <div class="w-full text-center">
                                <button type="button" class="modal-open btn-action--blue" title="'. __('translation.Images') .'" title="" x-on:click="showImageModal=true" data-id="' . $row->id . '" id="BtnImage">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button type="button" class="modal-open btn-action--green" title="'. __('translation.Edit') .'" x-on:click="showEditModal=true" data-id="' . $row->id . '" id="BtnUpdate">
                                    <i class="fas fa-pencil-alt"></i>
                                </button>
                                <button type="button" class="btn-action--red" title="'. __('translation.Delete') .'" data-id="' . $row->id . '" id="BtnDelete">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </div>
                        ';
                    })
                    ->rawColumns(['details', 'actions'])
                    ->skipPaging(true)
                    ->setTotalRecords($stockLogsCount)
                    ->make(true);

            return $table;
        }
    }

    public function create()
    {
        $data = [];
        Session::put('itemDefectArray',$data);
        $products = Product::where('seller_id',Auth::user()->id)->get();

        return view('qrCode.defect_stock.create', compact('products'));
    }

    public function getQrCodeProductForDefectStock(Request $request)
    {
        if(!empty($request->product_code)){

            $product = Product::with('getQuantity')
                ->where('seller_id',Auth::user()->id)
                ->where(function($query) use ($request) {
                    $query->where('product_code', $request->product_code)
                        ->orWhere('product_name', $request->product_code);
                })
                ->first();

            if(empty($product))
            {
                return null;
            }
            $sessionData = Session::get('itemDefectArray');
            if(!empty($sessionData))
            {
                foreach($sessionData as $item)
                {
                    $product2 =Product::with('getQuantity')
                        ->where('seller_id',Auth::user()->id)
                        ->where(function($query) use ($request){
                            $query->where('product_code',$request->product_code)
                                ->Orwhere('product_name',$request->product_code);
                        })
                        ->first();

                    if(strtolower($item) == strtolower($product2->product_code))
                    {
                        return null;
                    }
                }
            }
            Session::forget('itemDefectArray');
            array_push($sessionData,$product->product_code);
            Session::put('itemDefectArray',$sessionData);
            return response()->json($product);
        }
    }

    public function defectStockAutocomplete(Request $request){

        $search = $request->search;

        if($search == ''){
            $autocompletes = Product::orderby('id','asc')
                ->select('id','product_code')
                ->where('seller_id',Auth::user()->id)
                ->limit(5)->get();
        }else{
            $autocompletes = Product::orderby('id','asc')->where('seller_id',Auth::user()->id)
                ->where(function($query) use ($search){
                    $query->where('product_code', 'like', '%' .$search . '%')
                        ->Orwhere('product_name', 'like', '%' .$search . '%');
                })
                ->limit(5)
                ->get();

        }

        $response = array();
        foreach($autocompletes as $autocomplete){
            $response[] = array("value"=>$autocomplete->product_code,"label"=>$autocomplete->product_name." (".$autocomplete->product_code.")");
        }

        echo json_encode($response);
        exit;
    }

    public function resetQrCodeDefectProduct(){
        if(Session::has('itemDefectArray')){
            Session::forget('itemDefectArray');
            $data = [];
            Session::put('itemDefectArray',$data);
        }
    }

    public function deleteSessionDefectProduct(Request $request){
        if(Session::has('itemDefectArray')){
            $sessionData = Session::get('itemDefectArray');
            foreach($sessionData as $key=>$item)
            {
                if(strtolower($item) == strtolower($request->product_code))
                {
                    unset($sessionData[$key]);
                }
            }
            Session::forget('itemDefectArray');
            Session::put('itemDefectArray',$sessionData);
            return true;
        }
    }

    public function store(Request $request)
    {
//        dd($request);
        $input = false;
        if(count($request->product_id)>0)
        {
            foreach($request->product_id as $key=>$row) {
                $product_id = $row;
                $stockLog = new StockLog();
                $stockLog->product_id = $product_id;
                $stockLog->is_defect = 1;
                $stockLog->quantity = $request->quantity[$key];
                $stockLog->deffect_status = $request->status[$key];
                $stockLog->deffect_note = $request->note[$key];
                $stockLog->defect_result = $request->result[$key];
                $stockLog->seller_id = Auth::User()->id;
                $stockLog->date = Carbon::now(config('app.timezone'))->format('Y-m-d H:i');
                $stockLog->save();

                $input = true;
            }

            if ($request->hasFile('defect-image')) {
                foreach ($request->file('defect-image') as $file) {
                    $defect_image = new StockProductDeffectImage();
                    $defect_image->stock_id = $stockLog->id;
                    $defect_image->product_id = $product_id;
                    $defect_image->staff_id = $stockLog->staff_id;
                    $defect_image->date = Carbon::now(config('app.timezone'))->format('Y-m-d H:i');

                    $file_name = time() . '_' . Str::random(10) . '_' . $file->getClientOriginalName();
                    $destination_path = public_path('uploads/defectProduct');
                    $file->move($destination_path, $file_name);
                    $defect_image->image = 'uploads/defectProduct/' . $file_name;
                    $defect_image->save();
                }
            }
        }
        if ($input) {
            return redirect('defect-stock')->with('success', 'Defect Product added successfully created.');
        } else {
            return redirect('defect-stock')->with('error', 'Something went wrong.');
        }
    }

    public function show(Request $request)
    {
        $defectProduct = StockLog::find($request->id);
        $defectImages = StockProductDeffectImage::where('stock_id', $request->id)->get();
        return view('elements.show-defect-product', compact('defectProduct', 'defectImages'));
    }

    public function showResult(Request $request)
    {
        $defectProduct = StockLog::find($request->id);
        return view('elements.show-defect-product-text', compact('defectProduct'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required',
            'quantity' => 'required|numeric',
        ]);
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }
        if($request->quantity > 0)
        {
            $defect_stock = StockLog::find($request->id);

                $defect_stock->deffect_status = $request->status;
                $defect_stock->deffect_note = $request->deffect_note;
                $defect_stock->defect_result = $request->defect_result;
                if ($defect_stock->quantity != $request->quantity) {
                    $defect_stock->quantity = $request->quantity;
                }
                if ($request->hasFile('defect-image')) {
                    foreach ($request->file('defect-image') as $file) {
                        $defect_image = new StockProductDeffectImage();
                        $defect_image->stock_id = $defect_stock->id;
                        $defect_image->product_id = $defect_stock->product_id;
                        $defect_image->staff_id = $defect_stock->staff_id;
                        $defect_image->date = Carbon::now('Asia/Jakarta')->format('Y-m-d H:i A');

                        $upload_name = time() . '_' . Str::random(10) . '_' . $file->getClientOriginalName();
                        $destinationPath = public_path('uploads/defectProduct');
                        $file->move($destinationPath, $upload_name);
                        $defect_image->image = 'uploads/defectProduct/' . $upload_name;
                        $defect_image->save();
                    }
                }
                $result = $defect_stock->update();

                if ($result) {
                    return redirect()->back()->with('success', 'Defect Product has been updated successfully');
                } else {
                    return redirect()->back()->with('error', 'Update is unsuccessful.');
                }
        }
        else
            return redirect()->back()->with('danger', 'Quantity must be greater then zero');
    }

    public function addGallery(Request $request)
    {
        if($request->TotalImages > 0)
        {
            // Declare array varibale to store images data
            $imagenames = array();
            for ($x = 0; $x < $request->TotalImages; $x++) {
                if ($request->hasFile('images'.$x)) {

                    //Image Validation Rule
                    $rules1 = ['images'.$x =>'mimes:jpeg,jpg,png'];
                    $validator = Validator::make($request->all(), $rules1);
                    //Not valid image extention
                    if ($validator->fails()) {
                        return response()->json(["message" => "Image type must be a: jpeg, jpg, png.", 'type' => 'error']);
                    }

                    $file      = $request->file('images'.$x);
                    $filename  = $file->getClientOriginalName();
                    $extension = $file->getClientOriginalExtension();
                    $picture   = date('His').'-'.$filename;
                    $file->move(public_path('img/gallery-images'), $picture);

                    // Store Images in array variable
                    $imagenames[] = asset('/img/gallery-images/'.$picture);

                }

            }

            //Image Preview Response
            return response()->json(['type' => 'previewimage', 'imagenames' => $imagenames]);
        }

        //Empty Imput
        else
        {
            return response()->json(["message" => "Please select image first.", 'type' => 'error']);
        }
//        $validation = Validator::make($request->all(), [
//            'select_file' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048'
//        ]);
//        if($validation->passes())
//        {
//            $image = $request->file('select_file');
//
//            $defect_image = new StockProductDeffectImage();
//            $defect_image->stock_id = 121;
//            $defect_image->product_id = 2325;
//            $defect_image->staff_id = 15;
//            $defect_image->save_status = 1;
//            $defect_image->date = Carbon::now('Asia/Jakarta')->format('Y-m-d H:i A');
//
//            $upload_name = time() . '_' . Str::random(10) . '_' . $image->getClientOriginalName();
//            $destinationPath = public_path('uploads/defectProduct');
//            $image->move($destinationPath, $upload_name);
//            $defect_image->image = 'uploads/defectProduct/' . $upload_name;
//            $defect_image->save();
//
//            return response()->json([
//                'message'   => 'Image Upload Successfully',
//                'uploaded_image' => '<img src="/images/'.$upload_name.'" class="img-thumbnail" width="300" />',
//                'class_name'  => 'alert-success'
//            ]);
//        }
//        else
//        {
//            return response()->json([
//                'message'   => $validation->errors()->all(),
//                'uploaded_image' => '',
//                'class_name'  => 'alert-danger'
//            ]);
//        }

//        if($request->TotalImages > 0)
//        {
//            // Declare array varibale to store images data
//            $imagenames = array();
//            for ($x = 0; $x < $request->TotalImages; $x++) {
//                if ($request->hasFile('images'.$x)) {
//
//                    //Image Validation Rule
//                    $rules1 = ['images'.$x =>'mimes:jpeg,jpg,png'];
//                    $validator = Validator::make($request->all(), $rules1);
//                    //Not valid image extention
//                    if ($validator->fails()) {
//                        return response()->json(["message" => "Image type must be a: jpeg, jpg, png.", 'type' => 'error']);
//                    }
//
//                    $file      = $request->file('images'.$x);
//                    $filename  = $file->getClientOriginalName();
//                    $extension = $file->getClientOriginalExtension();
//                    $picture   = date('His').'-'.$filename;
//                    $file->move(public_path('uploads/gallery-images'), $picture);
//
//                    $defect_image = new StockProductDeffectImage();
//                    $defect_image->stock_id = 121;
//                    $defect_image->product_id = 2325;
//                    $defect_image->staff_id = 15;
//                    $defect_image->save_status = 1;
//                    $defect_image->date = Carbon::now('Asia/Jakarta')->format('Y-m-d H:i A');
//
//                    $upload_name = time() . '_' . Str::random(10) . '_' . $file->getClientOriginalName();
//                    $destinationPath = public_path('uploads/defectProduct');
//                    $file->move($destinationPath, $upload_name);
//                    $defect_image->image = 'uploads/defectProduct/' . $upload_name;
//                    $defect_image->save();
//
//                    // Store Images in array variable
//                    $imagenames[] = asset('/img/gallery-images/'.$upload_name);
//
//                }
//
//            }
//
//            //Image Preview Response
//            return response()->json(['type' => 'previewimage', 'imagenames' => $imagenames]);
//        }
//
//        //Empty Imput
//        else
//        {
//            return response()->json(["message" => "Please select image first.", 'type' => 'error']);
//        }





//        return response()->json([
//            'name'          => $upload_name,
//            'original_name' => $file->getClientOriginalName(),
//        ]);



    }

    public function defectStockDelete(Request $request)
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

            $defect_stock = StockLog::find($request->id);

            $defectImages = StockProductDeffectImage::where('stock_id', $request->id)->get();
            foreach ($defectImages as $defectImage) {
                if (file_exists($defectImage->image)) {
                    unlink($defectImage->image);
                    $defectImage->delete();
                }
            }

            //add quantity back to main stock
//            $mainStock = ProductMainStock::where('product_id', $defect_stock->product_id)->first();
//            if ($mainStock){
//                $mainStock->quantity = $mainStock->quantity + $defect_stock->quantity;
//                $mainStock->update();
//            }

            StockLog::where('id', $request->id)->delete();

            return [
                'status' => 1
            ];
        }
    }

    public function uploadDropzone(Request $request)
    {
        $image = $request->file('file');

        $imageName = time() . '.' . $image->extension();

        $image->move(public_path('uploads/images'), $imageName);

        return response()->json(['success' => $imageName, 'type' => 'previewimage']);
    }

    public function fetchDropzone()
    {
        $images = \File::allFiles(public_path('uploads/images'));
        $output = '<div class="row">';
        foreach($images as $image)
        {
            $output .= '
      <div class="col-md-2" style="margin-bottom:16px;" align="center">
                <img src="'.asset('uploads/images/' . $image->getFilename()).'" class="img-thumbnail" width="175" height="175" style="height:175px;" />
                <button type="button" class="btn btn-link remove_image" id="'.$image->getFilename().'">Remove</button>
            </div>
      ';
        }
        $output .= '</div>';
        echo $output;
    }

    public function deleteDropzone(Request $request)
    {
        if($request->get('name'))
        {
            \File::delete(public_path('uploads/images/' . $request->get('name')));
        }
    }

    public function addImages(Request $request)
    {
        return view('elements.form-save-defect-product-image');
    }

    public function fileUpload(Request $req){
        $req->validate([
            'imageFile' => 'required',
            'imageFile.*' => 'mimes:jpeg,jpg,png,gif,csv,txt,pdf|max:2048'
        ]);

        if($req->hasfile('imageFile')) {
            foreach($req->file('imageFile') as $file)
            {
                $name = $file->getClientOriginalName();
                $file->move(public_path().'/uploads/', $name);
                $imgData[] = $name;
            }

            $fileModal = new Image();
            $fileModal->name = json_encode($imgData);
            $fileModal->image_path = json_encode($imgData);


            $fileModal->save();

            return back()->with('success', 'File has successfully uploaded!');
        }
    }

}
