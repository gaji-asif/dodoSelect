<?php

namespace App\Http\Controllers;

use App\Models\ExchangeRate;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ExchangeRateController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $data = ExchangeRate::all();
        $title = 'Exchange Rate';
        return view('settings.exchange-rate',compact('data', 'title'));
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
        $exchangerate = new ExchangeRate();
        $exchangerate->name = $request->name;
        $exchangerate->rate = $request->rate;
        $exchangerate->seller_id = Auth::user()->id;
        $exchangerate->save();

        if($exchangerate)
        {
            return redirect()->back()->with('success','Exchange Rate has been added successfully');
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
        $exchangerate = ExchangeRate::find($id);
        $exchangerate->name = $request->name;
        $exchangerate->rate = $request->rate;
        $exchangerate->seller_id = Auth::user()->id;
        $exchangerate->update();

        if($exchangerate)
        {
            return redirect()->back()->with('success','Exchange Rate has been updated successfully');
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
        $exchangerate = ExchangeRate::where('id',$id)->where('seller_id',Auth::user()->id)->first();
        $exchangerate->delete();
        if($exchangerate){
            return redirect()->back()->with('success','Exchange Rate has been deleted successfully');
        }
        else{
            return redirect()->back()->with('danger','Something went wrong');
        }
    }
}
