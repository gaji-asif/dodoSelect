<?php

namespace App\Http\Controllers;

use App\Models\Package;
use App\Models\Product;
use App\Models\StockLog;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Carbon;
use Yajra\DataTables\Facades\DataTables;

class SellerController extends Controller
{
    public function dashboard()
    {
        $sellerId = Auth::user()->id;

        $yesterday = Carbon::now()->subDay()->format('Y-m-d');
        $today = Carbon::now()->format('Y-m-d');

        $stockLogs = StockLog::query()
            ->where('seller_id', $sellerId)
            ->selectRaw("HOUR(date) AS hour, SUM(quantity) AS quantity, check_in_out")
            ->byDateRange($today, $today)
            ->groupBy(DB::raw("check_in_out, HOUR(date)"))
            ->orderBy(DB::raw("check_in_out"), 'asc')
            ->orderBy(DB::raw("HOUR(date)"), 'asc')
            ->get();

        $stock_logs_add_data = [];
        $stock_logs_remove_data = [];

        for ($hour = 0; $hour <= 23; $hour++) {
            $addStock = $stockLogs
                ->where('hour', $hour)
                ->where('check_in_out', StockLog::CHECK_IN_OUT_ADD)
                ->first();

            $removeStock = $stockLogs
                ->where('hour', $hour)
                ->where('check_in_out', StockLog::CHECK_IN_OUT_REMOVE)
                ->first();

            $stock_logs_add_data[] = [
                'datetime' => date('Y-m-d H:i:s', strtotime($hour . ':00:00')),
                'quantity' => $addStock->quantity ?? 0
            ];

            $stock_logs_remove_data[] = [
                'datetime' => date('Y-m-d H:i:s', strtotime($hour . ':00:00')),
                'quantity' => $removeStock->quantity ?? 0
            ];
        }

        return view('seller.dashboard', compact(['stock_logs_add_data', 'stock_logs_remove_data']));
    }


    public function package()
    {
        $packages = DB::table('packages')
            ->get();

        return view('seller.package', compact('packages'));
    }


    public function data(Request $request)
    {
        if ($request->ajax()) {

            if (isset($request->id) && $request->id != null) {
                $data = Package::where([
                    'id' => $request->id
                ])->first();

                $id = $request->id;

                return view('elements.form-update-package', compact(['data', 'id']));
            }


            $data = package::orderBy('id', 'desc')->get();


            $table = Datatables::of($data)
                ->addColumn('price', function ($row) {
                    return currency_symbol('THB') .$row->price;
                })
                ->addColumn('package_type', function ($row) {
                    if($row->package_type == 1)
                    {
                        return 'Daily';
                    }
                    else
                    {
                        return 'Monthly';
                    }
                })
                ->addColumn('manage', function ($row) {
                    return '<span x-on:click=" showEditModal=true"class="modal-open bg-green-500 text-white rounded px-2 py-1 mr-4 capitalize cursor-pointer" data-id="' . $row->id . '" id="BtnUpdate">Edit</span><span class="bg-red-500 text-white rounded px-2 py-1 capitalize cursor-pointer" data-id="' . $row->id . '" id="BtnDelete">Delete</span>';
                })

                ->rawColumns(['manage'])
                ->make(true);
            return $table;
        }
    }

    public function insert(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|',
            'price' => 'required',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        Package::create([
            'package_name' => $request->name,
            'price' => $request->price,
            'details' => $request->details,
            'max_limit' => $request->max_limit,
            'package_type' => $request->package_type
        ]);

        return redirect()->back()->with('success', 'Package successfully created');
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|',
            'price' => 'required',
        ],);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        DB::table('packages')
            ->where('id', $request->id)
            ->update([
                'package_name' => $request->name,
                'price' => $request->price,
                'details' => $request->details,
                'max_limit' => $request->max_limit,
                'package_type' => $request->package_type
            ]);

        if ($request->password) {
            return $this->changePassword($request);
        }

        return redirect('/admin/package')->with('success', 'Package successfully updated');
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

            DB::table('packages')->where([
                'id' => $request->id
            ])->delete();

            return [
                'status' => 1
            ];
        }
    }

}
