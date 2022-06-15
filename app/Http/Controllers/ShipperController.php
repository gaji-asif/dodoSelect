<?php

namespace App\Http\Controllers;

use App\Models\Shipper;
use App\Models\ShippingCost;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Datatables;
use Illuminate\Support\Facades\Auth;

class ShipperController extends Controller
{
    public function index()
    {
    	return view('admin.manage-shipper');
    }

    public function insert(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required'
        ], [
            'name.required' => 'Column name is required'
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInpu();
        }

        DB::table('shippers')->insert([
            'name' => $request->name,
            'seller_id' => Auth::user()->id
        ]);

        return redirect()->back()->with('success', 'Data successfully created');
    }

    /**
     * Handle manage shipper server-side datatable
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function data(Request $request)
    {
        if (isset($request->id) && $request->id != null) {
            $shipperId = $request->id;
            $sellerId = Auth::user()->id;

            $data = Shipper::where('id', $shipperId)
                        ->where('seller_id', $sellerId)
                        ->first();

            $id = $shipperId;

            return view('elements.form-update-shipper', compact(['data', 'id']));
        }


        $sellerId = Auth::user()->id;

        $start = $request->get('start', 0);
        $limit = $request->get('length', 10);
        if ($limit < 1 OR $limit > 100) {
            $limit = 100;
        }

        $search = isset($request->get('search')['value'])
                ? $request->get('search')['value']
                : null;

        $orderColumnList = [
            'id',
            'name',
            'total_shipping_cost'
        ];

        $orderColumnIndex = isset($request->get('order')[0]['column'])
                            ? $request->get('order')[0]['column']
                            : 0;
        $orderColumnDir = isset($request->get('order')[0]['dir'])
                            ? $request->get('order')[0]['dir']
                            : 'asc';

        $orderColumn = isset($orderColumnList[$orderColumnIndex])
                        ? $orderColumnList[$orderColumnIndex]
                        : 'name';

        $data = Shipper::where('seller_id', $sellerId)
                        ->totalShippingCost()
                        ->searchTable($search)
                        ->skip($start)
                        ->take($limit)
                        ->orderBy($orderColumn, $orderColumnDir)
                        ->get();

        $table = Datatables::of($data)
                    ->addColumn('totalPackage', function ($row) {
                       return number_format($row->total_shipping_cost);
                    })
                    ->addColumn('manage', function ($row) {
                        return '
                            <button x-on:click="showEditModal=true" class="btn-action--green modal-open" data-id="' . $row->id . '" id="BtnUpdate">
                                '. __('translation.Edit') .'
                            </button>
                            <button class="btn-action--red" data-id="' . $row->id . '" id="BtnDelete">
                                '. __('translation.Delete') .'
                            </button>
                            <a href="'. route('add-shipping-cost', [ 'id' => $row->id ]) .'" class="btn-action--blue">
                                '. __('translation.Add Cost') .'
                            </a>';
                    })
                    ->rawColumns(['manage'])
                    ->make(true);

        return $table;
        // }
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
        }

        $orders = DB::table('orders')->where('shipper_id',$request->id)->count();

        if ($orders > 0) {
            return [
                'status' => 2,
                'failed' => 'Data failed deleted'
            ];
        }

        DB::table('shippers')->where('id',$request->id)->delete();

        return [
            'status' => 1
        ];
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required',
            'name' => 'required'
        ], [
            'id.required' => 'Column id is required',
            'name.required' => 'Column name is required'
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInpu();
        }

        DB::table('shippers')->where('id',$request->id)->update([
            'name' => $request->name,
            'seller_id' => Auth::user()->id
        ]);

        return redirect()->back()->with('success', 'Data successfully updated');
    }
}
