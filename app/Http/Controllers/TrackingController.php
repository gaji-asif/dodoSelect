<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\Order;
use Carbon\Carbon;
use Datatables;
use Auth;

class TrackingController extends Controller
{
    public function index()
    {
        $shippers = DB::table('shippers')
        ->get();
        return view('seller.manage-tracking', compact('shippers'));
    }

    public function data(Request $request)
    {
        if ($request->ajax()) {

            $shippers = DB::table('shippers')
            ->get();

            if (isset($request->id) && $request->id != null) {
                $data = DB::table('orders')
                ->join('shippers', 'orders.shipper_id', 'shippers.id')
                ->select('orders.*', 'shippers.name as shipper')
                ->where([
                    'orders.id' => $request->id,
                    'shop_id' => Auth::user()->shop_id
                ])->first();

                $id = $request->id;

                return view('elements.form-update-tracking', compact(['data', 'shippers', 'id']));
            }

            if (isset($request->date) && $request->date != null) {

                $data = Order::
                join('shippers', 'orders.shipper_id', 'shippers.id')
                ->select('orders.*', 'shippers.name as shipper')
                ->where('shop_id', Auth::user()->shop_id)
                ->where('date', $request->date)
                ->orderBy('id', 'desc')
                ->get();
            } else {

                $data = Order::
                join('shippers', 'orders.shipper_id', 'shippers.id')
                ->select('orders.*', 'shippers.name as shipper')
                ->where('shop_id', Auth::user()->shop_id)
                ->where('date', today('Asia/Jakarta')->toDateString())
                ->orderBy('id', 'desc')
                ->get();
            }

            $table = Datatables::of($data)
            ->addColumn('date', function($row) {
                return $row->date->format('d-m-Y').' '.$row->time->format('H:i');
            })
            ->addColumn('manage', function ($row) {
                return '<span x-on:click=" showEditModal=true"class="modal-open bg-green-500 text-white rounded px-2 py-1 mr-4 capitalize cursor-pointer" data-id="' . $row->id . '" id="BtnUpdate">Edit</span><span class="bg-red-500 text-white rounded px-2 py-1 capitalize cursor-pointer" data-id="' . $row->id . '" id="BtnDelete">Delete</span>';
            })
            ->rawColumns(['manage'])
            ->make(true);
            return $table;
        }
    }

    public function store(Request $request)
    {
        $input = $request->all();

        $validator = $this->validateInput($input);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        } else {
            DB::table('orders')
            ->insert([
                'shipper_id' => $input['shipper'],
                'shop_id' => Auth()->user()->shop_id,
                'tracking_id' => $input['tracking-id'],
                'buyer' => $input['name'],
                'input_method' => 'manual',
                'phone' => $input['phone'],
                'date' => today('Asia/Jakarta')->toDateString(),
                'time' => now('Asia/Jakarta')->toTimeString()
            ]);

            return redirect()->back()->with('success', 'Data successfully created');
        }
    }
    
    public function update(Request $request)
    {
        $input = $request->all();

        $validator = $this->validateInput($input);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        } else {
            DB::table('orders')
            ->where([
                'id' => $input['id'],
                'shop_id' => Auth::user()->shop_id
            ])
            ->update([
                'shipper_id' => $input['shipper'],
                'tracking_id' => $input['tracking-id'],
                'buyer' => $input['name'],
                'phone' => $input['phone'],
            ]);

            return redirect()->back()->with('success', 'Data successfully updated');
        }
    }

    public function delete(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($request->all(), [
            'id' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 0,
                'message' => 'Field id is required'
            ]);
        } else {

            DB::table('orders')->where([
                'shop_id' => Auth::user()->shop_id,
                'id' => $request->id
            ])->delete();

            return [
                'status' => 1
            ];
        }
    }

    public function import(Request $request)
    {
        $input = $request->all();

        // $validator = Validator::make($input, [
        //     'name' => 'required|string',
        //     'trackingId' => 'required|string',
        //     'shipper' => 'required|numeric'// |exists'// :shippers,id',
        // ], [
        //     'required' => 'Please fill :attribute',
        //     'string' => 'The :attribute must be valid value',
        //     'numeric' => 'The :attribute must be valid value',
        //     // 'exists' => "Youre choose wrong shipper"
        // ]);

        if (false) {
            // return error here
            // return response()->json($validator, 419);
        } else {

            foreach ($request->data[0] as $data) {

                DB::table('orders')->insert([
                    'shipper_id' => $request->shipper,
                    'shop_id' => Auth()->user()->shop_id,
                    'tracking_id' => $data['trackingId'],
                    'buyer' => $data['name'],
                    'phone' => $data['phone'],
                    'input_method' => 'import',
                    'date' => today('Asia/Jakarta')->toDateString(),
                    'time' => now('Asia/Jakarta')->toTimeString()
                ]);
            }
        }

        return response()->json([
            'status' => 1
        ], 200);
    }

    public function validateInput(array $input)
    {
        // validating tracking input
        $rules = [
            'name' => 'required|string',
            'tracking-id' => 'required|string',
            'shipper' => 'required|numeric|exists:shippers,id',
        ];
        $message = [
            'required' => 'Please fill :attribute',
            'string' => 'The :attribute must be valid value',
            'numeric' => 'The :attribute must be valid value',
            'exists' => "Youre choose wrong shipper"
        ];
        $attributes = [
            'tracking-id' => 'Tracking Id'
        ];

        $validator = Validator::make($input, $rules, $message, $attributes);

        return $validator;
    }

    public function trackPage()
    {
        return view('seller.track');
    }
}
