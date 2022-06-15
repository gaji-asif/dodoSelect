<?php

namespace App\Http\Controllers;

use App\Models\Shipper;
use App\Models\ShippingCost;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ShippingCostController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($id)
    {
        $data = ShippingCost::where('shipper_id',$id)->where('seller_id',Auth::user()->id)->get();
        $shipper_id = $id;
        $shippingCompany = Shipper::where('seller_id',Auth::user()->id)->where('id',$shipper_id)->first();

        return view('seller.shipping.shippingCost',compact('data','shipper_id','shippingCompany'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $shippingCost = new ShippingCost();
        $shippingCost->name = $request->name;
        $shippingCost->shipper_id = $request->shipper_id;
        $shippingCost->weight_from = $request->weight_from;
        $shippingCost->weight_to = $request->weight_to;
        $shippingCost->price = $request->price;
        $shippingCost->seller_id = Auth::user()->id;
        $shippingCost->save();

        if ($shippingCost) {
            return redirect()->back()->with('success','Shipping Cost Added Successfully');
        }

        return redirect()->back()->with('danger','Something happened wrong');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function shippingCostEdit($id,$shipper_id)
    {
        $data = ShippingCost::where('shipper_id',$shipper_id)->where('seller_id',Auth::user()->id)->get();
        $editData = ShippingCost::where('id',$id)->where('seller_id',Auth::user()->id)->first();
        $shippingCompany = Shipper::where('seller_id',Auth::user()->id)->where('id',$shipper_id)->first();

        return view('seller.shipping.shippingCost',compact('data','editData','shipper_id','shippingCompany'));
    }

    /**
     * Update shipping cost
     *
     * @return \Illuminate\Http\Response
     */
    public function updateShippingCost(Request $request, $id)
    {
        $shippingCost = ShippingCost::find($id);
        $shippingCost->name = $request->name;
        $shippingCost->weight_from = $request->weight_from;
        $shippingCost->weight_to = $request->weight_to;
        $shippingCost->price = $request->price;
        $shippingCost->seller_id = Auth::user()->id;
        $shippingCost->save();

        if ($shippingCost) {
            return redirect('add-cost/'.$request->shipper_id)->with('success','Shipping Cost Updated Successfully');
        }

        return redirect('add-cost/'.$request->shipper_id)->with('danger','Something happened wrong');
    }

    /**
     * Update shipping cost
     *
     * @return \Illuminate\Http\Response
     */
    public function delete($id)
    {
        $shippingCost = ShippingCost::where('id',$id)->where('seller_id',Auth::user()->id)->delete();

        if ($shippingCost){
            return redirect()->back()->with('success','Shipping Cost Deleted Successfully');
        }

        return redirect()->back()->with('danger','Something happened wrong');
    }
}
