<?php

namespace App\Http\Controllers;

use App\Models\ShipType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ShipTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $data = ShipType::all();
        $title = 'ship-types';
        return view('settings.ship-types',compact('data', 'title'));
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
        $shiptype = new ShipType();
        $shiptype->name = $request->name;
        $shiptype->save();

        if($shiptype)
        {
            return redirect()->back()->with('success','Ship type has been added successfully');
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
        $shiptype = ShipType::find($id);
        $shiptype->name = $request->name;
        $shiptype->update();

        if($shiptype)
        {
            return redirect()->back()->with('success','Ship type been updated successfully');
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
        $shiptype = ShipType::where('id',$id)->first();
        $shiptype->delete();
        if($shiptype){
            return redirect()->back()->with('success','Ship type has been deleted successfully');
        }
        else{
            return redirect()->back()->with('danger','Something went wrong');
        }
    }
}
