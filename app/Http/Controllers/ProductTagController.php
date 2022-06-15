<?php

namespace App\Http\Controllers;

use App\Models\ProductTag;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProductTagController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $data = ProductTag::all();
        $title = 'product-tag';
        return view('settings.product-tag',compact('data', 'title'));
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
        $product_tag = new ProductTag();
        $product_tag->name = $request->name;
        $product_tag->seller_id = Auth::user()->id;
        $result = $product_tag->save();

        if($result)
        {
            return redirect()->back()->with('success','New product tag has been added.');
        }
        else{
            return redirect()->back()->with('danger','Something went wrong');
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
        $product_tag = ProductTag::find($id);
        $product_tag->name = $request->name;
        $product_tag->seller_id = Auth::user()->id;
        $result = $product_tag->update();

        if($result)
        {
            return redirect()->back()->with('success','Product tag has been updated.');
        }
        else{
            return redirect()->back()->with('danger','Something went wrong');
        }
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

    public function delete($id)
    {
        $product_tag = ProductTag::where('id',$id)->where('seller_id',Auth::user()->id)->first();
        $result = $product_tag->delete();

        if($result){
            return redirect()->back()->with('success','Product tag has been deleted.');
        }
        else{
            return redirect()->back()->with('danger','Something went wrong');
        }
    }
}
