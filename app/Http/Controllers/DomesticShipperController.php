<?php

namespace App\Http\Controllers;

use App\Models\DomesticShipper;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DomesticShipperController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $data = DomesticShipper::all();
        $title = 'Domestic Shipper';
        return view('seller.purchase_order.settings.domestic_shippers',compact('data', 'title'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $domestic_shipper = new DomesticShipper();
        $domestic_shipper->name = $request->name;
        $domestic_shipper->save();

        if($domestic_shipper)
        {
            return redirect()->back()->with('success','Domestic Shipper has been added successfully');
        }
        else{
            return redirect()->back()->with('danger','Something wnet wrong');
        }
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
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        $domestic_shipper = DomesticShipper::find($id);
        $domestic_shipper->name = $request->name;
        $domestic_shipper->update();

        if($domestic_shipper)
        {
            return redirect()->back()->with('success','Domestic Shipper has been updated successfully');
        }
        else{
            return redirect()->back()->with('danger','Something went wrong');
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function delete($id)
    {
        $domestic_shipper = DomesticShipper::where('id',$id)->first();
        $domestic_shipper->delete();
        if($domestic_shipper){
            return redirect()->back()->with('success','Domestic Shipper has been deleted successfully');
        }
        else{
            return redirect()->back()->with('danger','Something went wrong');
        }
    }
}
