<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use App\Models\SupplierContact;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class SupplierController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $data = Supplier::where('seller_id',Auth::user()->id)->get();
        $title = 'supplier';

        return view('settings.supplier',compact('data', 'title'));
    }

    public function data(Request $request)
    {
        if ($request->ajax()){
            if (isset($request->id) && $request->id != null) {
                $supplier = Supplier::where([
                    'id' => $request->id
                ])->first();
                return view('elements.form-update-supplier',compact( 'supplier'));
            }

            $data = Supplier::where('seller_id',Auth::user()->id)
                ->with('supplierContacts')
                ->get();

            $table = Datatables::of($data)
                ->addColumn('supplier_contact', function ($row) {
                    $supplierContact = '';
                    foreach ($row->supplierContacts as $contact){
                        $supplierContact .= ' <span class="font-bold"> ' . $contact->contact_channel . ' - </span>
                            <span class="">' . $contact->contact . '</span> <br> ';
                    }
                    return
                        '<div> ' . $supplierContact . '</div>';
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
                ->rawColumns(['supplier_contact','manage'])
                ->make(true);

            return $table;
        }
    }


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $supplier = new Supplier();
        $supplier->supplier_name = $request->supplier_name;
        $supplier->address = $request->address;
        $supplier->note = $request->note;
        $supplier->seller_id = Auth::user()->id;
        $supplier->save();

        if(isset($request->contact_channel) && count($request->contact_channel) > 0){
            foreach($request->contact_channel as $key=>$row){
                if ($row != null) {
                    $supplierContact = new SupplierContact();
                    $supplierContact->supplier_id = $supplier->id;
                    $supplierContact->contact_channel = $row;
                    $supplierContact->contact = $request->contact[$key];

                    $supplierContact->save();
                }
            }
        }

        if($supplier)
        {
            return redirect()->back()->with('success','Supplier Added Successfully');
        }
        else{
            return redirect()->back()->with('danger','Something happened wrong');
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
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required',
            'supplier_name' => 'required'
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }
        $supplier = Supplier::where('id', $request->id)->first();
        $supplier->supplier_name = $request->supplier_name;
        $supplier->address = $request->address;
        $supplier->note = $request->note;
        $supplier->seller_id = Auth::user()->id;
        $supplier->save();

        SupplierContact::where('supplier_id', $supplier->id)->delete();

        if(isset($request->contact_channel) && count($request->contact_channel) > 0){
            foreach($request->contact_channel as $key=>$row) {
                if ($row != null) {
                    $supplierContact = new SupplierContact();
                    $supplierContact->supplier_id = $supplier->id;
                    $supplierContact->contact_channel = $row;
                    $supplierContact->contact = $request->contact[$key];

                    $supplierContact->save();
                }
            }
        }

        if($supplier)
        {
            return redirect('suppliers')->with('success','Supplier Updated Successfully');
        }
        else{
            return redirect('suppliers')->with('danger','Something happened wrong');
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

            Supplier::where('id',$request->id)->where('seller_id',Auth::user()->id)->delete();

            return [
                'status' => 1
            ];
        }
    }
}
