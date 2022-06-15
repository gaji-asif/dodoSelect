<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;
use Datatables;

class AdminController extends Controller
{
    public function dashboard()
    {
        function dates($format)
        {
            $dates = [
                Carbon::now('Asia/Jakarta')->addDays(-6)->format($format),
                Carbon::now('Asia/Jakarta')->addDays(-5)->format($format),
                Carbon::now('Asia/Jakarta')->addDays(-4)->format($format),
                Carbon::now('Asia/Jakarta')->addDays(-3)->format($format),
                Carbon::now('Asia/Jakarta')->addDays(-2)->format($format),
                Carbon::now('Asia/Jakarta')->addDays(-1)->format($format),
                Carbon::now('Asia/Jakarta')->format($format)
            ];

            return $dates;
        }

        foreach (dates('Y-m-d') as $date) {
            $data[] = DB::table('orders')->where('date', $date)->count();
        }

        $dates = dates('d M');

        $orderCount = Order::query()
            ->whereBetween('date', [today('Asia/Jakarta')->subDays(6), today('Asia/Jakarta')])
            ->count();

        $productCount = Product::count();
        $staffCount = User::where('role','staff')->count();
        $sellerCount = User::where('role','member')->count();

        return view('admin.dashboard', compact(['data', 'dates', 'orderCount', 'productCount', 'staffCount', 'sellerCount']));
    }

    public function manageSeller()
    {
        $shippers = DB::table('shippers')
            ->get();
        return view('admin.manage-seller', compact('shippers'));
    }

    public function data(Request $request)
    {
        if ($request->ajax()) {

            if (isset($request->id) && $request->id != null) {
                $data = User::where([
                    'id' => $request->id
                ])->first();

                $id = $request->id;

                return view('elements.form-update-seller', compact(['data', 'id']));
            }


            $data = User::where('role', 'member')
                ->orderBy('id', 'desc')
                ->get();


            $table = Datatables::of($data)
                ->addColumn('created_at', function ($row) {
                    return $row->created_at->format('d-m-Y H:i');
                })
                ->addColumn('manage', function ($row) {
                    return '<span x-on:click=" showEditModal=true"class="modal-open bg-green-500 text-white rounded px-2 py-1 mr-4 capitalize cursor-pointer" data-id="' . $row->id . '" id="BtnUpdate">Edit</span><span class="bg-red-500 text-white rounded px-2 py-1 capitalize cursor-pointer" data-id="' . $row->id . '" id="BtnDelete">Delete</span>';
                })
                ->addColumn('orders_total', function ($row) {
                    return count($row->orders);
                })
                ->rawColumns(['manage'])
                ->make(true);
            return $table;
        }
    }

    public function insert(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'package' => 'required|max:4|unique:users|alpha',
            'name' => 'required|min:4',
            'email' => 'required|email|unique:users',
            'password' => 'required'
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        User::create([
            'phone' => $request->phone,
            'shop_id' => $request->shop_id,
            'name' => $request->name,
            'email' => $request->email,
            'role' => 'member',
            'password' => Hash::make($request->password),
            'is_active' => '1'
        ]);

        return redirect()->back()->with('success', 'Seller successfully created');
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'shop_id' => 'required|max:4|alpha',
            'name' => 'required',
            'email' => 'required|email'
        ],);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        DB::table('users')
            ->where('id', $request->id)
            ->update([
                'shop_id' => $request->shop_id,
                'name' => $request->name,
                'email' => $request->email,
                'is_active' => $request->is_active
            ]);

        if ($request->password) {
            return $this->changePassword($request);
        }

        return redirect('/admin/manage-seller')->with('success', 'Data successfully updated');
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

        $user = User::where('id', $request->id);
        $orders = DB::table('orders')->where('shop_id',$user->first()->shop_id)->get();

        if(count($orders) > 0 ) {
            return response()->json([
                'status' => 0,
                'message' => "Seller can't be deleted"
            ]);
        }

        $user->delete();
        return [
            'status' => 1
        ];
    }

    public function changePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'password' => 'min:8',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        User::where('id',$request->id)->update([
            'password' => Hash::make($request->password)
        ]);

        return redirect()->back()->with('success', 'Data successfully updated');
    }

    public function userLogo()
    {
        $users = User::where('role','member')->get();
        return view('admin.user_logo', compact('users'));
    }

    public function uploadUserLogo(Request $request)
    {
        $user = User::find($request->user_id);

        if(isset($user))
        {
            if ($request->hasFile('logo')) {
                $upload = $request->file('logo');
                $file_type = $upload->getClientOriginalExtension();
                $upload_name =  time() . $upload->getClientOriginalName();
                $destinationPath = public_path('uploads/user_logo');
                $upload->move($destinationPath, $upload_name);
                $user->logo = 'uploads/user_logo/'.$upload_name;
                $result = $user->save();
                if ($result) {
                    return redirect()->back()->with('success', 'User Logo update successfully');
                } else {
                    return redirect()->back()->with('error', 'Something Wrong Happened');
                }
            }
            else{
                return redirect()->back()->with('error', 'Please select a logo');
            }
        }
        else
        {
            return redirect()->back()->with('error', 'Please select A user');
        }
    }
}
