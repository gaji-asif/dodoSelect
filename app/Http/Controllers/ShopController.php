<?php

namespace App\Http\Controllers;

use App\Http\Requests\Shop\ShopStoreRequest;
use App\Http\Requests\Shop\ShopUpdateRequest;
use App\Models\Shop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Storage;

class ShopController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return array
     */
    public function index()
    {
        $title = 'shop';
        $data = Shop::where('seller_id',Auth::user()->id)->get();
        return view('settings.shop',compact('title','data'));
    }

    public function data(Request $request)
    {
        if (isset($request->id) && $request->id != null) {
            $editData = Shop::where([
                'id' => $request->id
            ])->first();
            return view('elements.form-update-shop',compact( 'editData'));
        }

        $data = Shop::where('seller_id',Auth::user()->id)->get();

        $table = Datatables::of($data)
            ->addColumn('str_name_code', function ($shop) {
                $shopCode = '<i>N/A</i>';
                if (! empty($shop->code)) {
                    $shopCode = $shop->code;
                }

                return '
                    <div>
                        <span class="font-bold">
                            '. $shop->name .'
                        </span><br>
                        <span>
                            Code: '. $shopCode .'
                        </span>
                    </div>
                ';
            })
            ->addColumn('image', function ($row) {

                if(Storage::disk('s3')->exists($row->logo)) {
                    return '<span><img src="'.Storage::disk('s3')->url($row->logo).'" style="width:60px;height:55px"></span>';

                }
                else{
                return '<span><img src="'.asset('No-Image-Found.png').'" style="width:60px;height:55px"></span>';
            }
            })
            ->addColumn('address_detail', function ($row) {
                return  $row->address . ' <br> '
                        . $row->sub_district . ', '
                        . $row->district . ' <br> '
                        . $row->province . ' '
                        . $row->postcode;
            })
            ->addColumn('manage', function ($row) {
                return '
                    <div class="w-full text-center">
                        <button type="button" class="modal-open btn-action--green" title="'. __('translation.Edit') .'" x-on:click="showEditModal=true" data-id="' . $row->id . '" id="BtnUpdate">
                            <i class="fas fa-pencil-alt"></i>
                        </button>
                        <button type="button" class="btn-action--red" title="'. __('translation.Delete') .'" data-id="' . $row->id . '" id="BtnDelete">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </div>
                ';
            })
            ->rawColumns(['str_name_code', 'image', 'address_detail','manage'])
            ->make(true);

        return $table;
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('elements.form-update-shop');

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\Shop\ShopStoreRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(ShopStoreRequest $request)
    {
        $shop = new Shop();
        $shop->name = $request->name;
        $shop->code = $request->code;
        $shop->address = $request->address;
        $shop->phone = $request->phone;

        $full_address = $request->full_address;
        if ($full_address != '') {
            $nameArr = explode('/', $full_address);
            $shop->district = $nameArr[0];
            $shop->sub_district = $nameArr[1];
            $shop->province = $nameArr[2];
            $shop->postcode = $nameArr[3];
        }

        $shop->username = $request->shop_username;
        $shop->password = $request->shop_password;

        $shop_credentials = [];
        if(isset($request->shopee)){
            $credentials = explode(',',$request->shopee_credentials);
            if(count($credentials) === 2){
                $shop_credentials["Shopee"] = [
                    "username" => trim($credentials[0]),
                    "password" => trim($credentials[1])
                ];
            }
        }

        if(isset($request->lazada)){
            $credentials = explode(',',$request->lazada_credentials);
            if(count($credentials) === 2){
                $shop_credentials["Lazada"] = [
                    "username" => trim($credentials[0]),
                    "password" => trim($credentials[1])
                ];
            }
        }

        $shop->shop_type = json_encode($shop_credentials);

        if ($request->hasFile('logo')) {
            $upload = $request->file('logo');
            $path =  Storage::disk('s3')->put('uploads/shop_logo', $upload,'public');
            $shop->logo = $path;
        }

        $shop->seller_id = Auth::user()->id;
        $shop->save();

        if ($shop) {
            return redirect()->back()->with('success','Shop Added Successfully');
        }

        return redirect()->back()->with('danger','Something happened wrong');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $data = Shop::where('seller_id',Auth::user()->id)->get();

        return view('settings.shop',compact('data'));
    }

    /**
     * Update the shop data.
     *
     * @param  \App\Http\Requests\Shop\ShopUpdateRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function update(ShopUpdateRequest $request)
    {
        $shop = Shop::where('id', $request->id)->first();
        $shop->name = $request->name;
        $shop->code = $request->code;
        $shop->address = $request->address;
        $shop->phone = $request->phone;

        $full_address = $request->full_address;
        if ($full_address != '') {
            $nameArr = explode('/', $full_address);
            $shop->district = $nameArr[0];
            $shop->sub_district = $nameArr[1];
            $shop->province = $nameArr[2];
            $shop->postcode = $nameArr[3];
        }

        $shop->username = $request->shop_username;
        $shop->password = $request->shop_password;

        $shop_credentials = [];
        if(isset($request->shopee)){
            $credentials = explode(',',$request->shopee_credentials);
            if(count($credentials) === 2){
                $shop_credentials["Shopee"] = [
                    "username" => trim($credentials[0]),
                    "password" => trim($credentials[1])
                ];
            }
        }

        if(isset($request->lazada)){
            $credentials = explode(',',$request->lazada_credentials);
            if(count($credentials) === 2){
                $shop_credentials["Lazada"] = [
                    "username" => trim($credentials[0]),
                    "password" => trim($credentials[1])
                ];
            }
        }

        $shop->shop_type = json_encode($shop_credentials);

        if ($request->hasFile('logo')) {
            if(Storage::disk('s3')->exists($shop->shop_logo)) {
                Storage::disk('s3')->delete($shop->shop_logo);
            }

            $upload = $request->file('logo');
            $path =  Storage::disk('s3')->put('uploads/shop_logo', $upload,'public');
            $shop->logo = $path;
        }

        $shop->seller_id = Auth::user()->id;
        $shop->save();

        if ($shop) {
            return redirect('shops')->with('success','Shop Updated Successfully');
        }

        return redirect('shops')->with('danger','Something happened wrong');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
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

            $shop = Shop::where('id',$request->id)->where('seller_id',Auth::user()->id)->first();
            if(file_exists($shop->logo)){
                unlink($shop->logo);
            }
            $shop->delete();

            return [
                'status' => 1
            ];
        }
    }
}
