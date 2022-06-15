<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductMainStock;
use App\Models\Role;
use App\Models\Shop;
use App\Models\StockLog;
use App\Models\User;
use Illuminate\Http\Request;
use Datatables;
use DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Carbon;
class StaffController extends Controller
{
    public function manageStaff()
    {
        $staffs  = User::Where('role','staff')->get();
        $roles = Role::all();
        $title = 'users';
        return view('seller.manage-staff', compact('staffs', 'roles', 'title'));
    }

    public function data(Request $request)
    {
        if ($request->ajax()) {
            if(isset($request->assign_shop)){
                $staff = User::find($request->id);
                $assigned_shops = '';
                if($staff->assigned_shops != null){
                    $assigned_shops = json_decode($staff->assigned_shops);
                }
                $data['staff_id'] = $request->id;
                $data['assigned_shops'] = $assigned_shops;
                $data['shops'] = Shop::where('seller_id', Auth::id())->get();
                return view('elements.form-assign-shop', $data);
            }

            if (isset($request->id) && $request->id != null) {
                $data = User::where([
                    'id' => $request->id
                ])->first();

                $id = $request->id;
                $roles = Role::all();

                return view('elements.form-update-staff', compact(['data', 'id', 'roles']));
            }

            $data = User::where('role', 'staff')
                ->where('seller_id', Auth::user()->id)
                ->orderBy('id', 'desc')
                ->get();

            $table = Datatables::of($data)
                ->addColumn('created_at', function ($row) {
                    return $row->created_at->format('d-m-Y H:i');
                })
                ->addColumn('staff_role', function ($row) {
                    return $row->getRoleNames()->first();
                })
                ->addColumn('manage', function ($row) {
                    if (Auth::user()->role == 'member'){
                        return '<div class="w-full text-center">
                                    <button type="button" class="modal-open btn-action--gray" title="'. __('translation.Assign Shop') .'" x-on:click="showAssignShopModal=true" data-id="' . $row->id . '" id="BtnShopUpdate">
                                       <i class="fas fa-anchor"></i>
                                    </button>
                                    <button type="button" class="modal-open btn-action--green" title="'. __('translation.Edit') .'" x-on:click="showEditModal=true" data-id="' . $row->id . '" id="BtnUpdate">
                                       <i class="fas fa-pencil-alt"></i>
                                    </button>
                                    <button type="button" class="btn-action--red" title="'. __('translation.Delete') .'" data-id="' . $row->id . '" id="BtnDelete">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                    <button type="button" class="modal-open btn-action--yellow" title="'. __('translation.Change Password') .'" title="" data-id="' . $row->id . '" id="BtnPasswordChange">
                                        <i class="fas fa-key"></i>
                                    </button>
                                </div>';
                    }
                    else{
                        return '<div class="w-full text-center">
                                    <button type="button" class="modal-open btn-action--green" title="'. __('translation.Edit') .'" x-on:click="showEditModal=true" data-id="' . $row->id . '" id="BtnUpdate">
                                        <i class="fas fa-pencil-alt"></i>
                                    </button>
                                    <button type="button" class="btn-action--red" title="'. __('translation.Delete') .'" data-id="' . $row->id . '" id="BtnDelete">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </div>';
                    }
                })
                ->rawColumns(['manage'])
                ->make(true);
            return $table;
        }
    }

    public function insert(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|min:4',
            'email' => 'required|email|unique:users',
            'phone' => 'required|unique:users|numeric',
            'password' => 'required|string|min:8',
            'role' => 'required'
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
            'role' => 'staff',
            'seller_id' => Auth::user()->id,
            'password' => Hash::make($request->password),
            'is_active' => '1'
        ]);

        $staff_role = Role::find($request->role);
        $user->roles()->attach($staff_role);

        return redirect()->back()->with('success', 'Staff successfully created');
    }

    public function update(Request $request)
    {
        $user = User::find($request->id);
        if(isset($request->assign_shops)){
            if($user->seller_id != Auth::id()){
                return redirect('manage-staff')->with('success', __('translation.Sorry, shop cannot be assigned'));
            }
            $user->assigned_shops = json_encode($request->assigned_shops);
            $user->update();
            return redirect('manage-staff')->with('success', __('translation.Shops assigned successfully'));
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $user->name = $request->name;
        $user->email = $request->email;
        $user->phone = $request->phone;
        $user->address = $request->address;
        $user->update();

        $staff_role = Role::find($request->role);
        $user->roles()->detach();
        $user->roles()->attach($staff_role);

        if ($request->password) {
            return $this->changePassword($request);
        }

        return redirect('manage-staff')->with('success', __('translation.Data successfully updated'));
    }

    public function changePasswordModal(Request $request)
    {
        $data = User::find($request->id);
        return view('elements.form-update-staff-password', compact('data'));
    }

    public function changePassword(Request $request)
    {
        $input = $request->all();
        $validator = $this->validatePassword($input);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        } else {

            $user = DB::table('users')->where([
                'id' => $request->id
            ])->first();

//            $currentPassword = $user->password;
//
//            // validate if inputted password same with current password
//            if (Hash::check($input['current-password'], $currentPassword)) {

                DB::table('users')->where('id', $request->id)
                    ->update([
                        'password' => Hash::make($input['new-password'])
                    ]);
                return redirect()->back()->with('success', 'Password successfully changed');
//            } else {
//                return redirect()->back()->with('error', 'Wrong Password');
//            }
        }
    }

    public function validatePassword(array $input)
    {
        // validating user input
        $rules = [
//            'current-password' => 'required|string',
            'new-password' => 'required|string|min:8', //|,confirmed',
        ];
        $message = [
            // 'confirmed' => 'Password confirmation is wrong'
        ];
        $attributes = [
            'new-password' => 'New Password'
        ];
        $validator = Validator::make($input, $rules, $message, $attributes);

        return $validator;
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

        $user = User::where('id', $request->id)->delete();
        return [
            'status' => 1
        ];
    }

    public function dashboard()
    {
        $user = Auth::user();
        $products = Product::where('seller_id',$user->seller_id)->get();
        return view('staff.dashboard', compact(['products']));

    }

    public function quantityUpdate()
    {
        $user = Auth::user();
        $products = DB::table('products')->where('seller_id',$user->seller_id)->get();
        return view('staff.product', compact('products'));
    }

    public function productData(Request $request)
    {
        if ($request->ajax()) {

            if (isset($request->id) && $request->id != null) {
                $data = Product::where([
                    'id' => $request->id
                ])->first();

                $id = $request->id;

                return view('elements.form-update-quantity', compact(['data', 'id']));
            }

            $user = Auth::user();
            // dd($user);
            $data = Product::where('seller_id',$user->seller_id)->with('getQuantity')->orderBy('id', 'desc')->get();

            $table = Datatables::of($data)

                ->addColumn('image', function ($row) {
                    if(!empty($row->image))
                    {
                        return '<span><img src="'.asset($row->image).'" class="cutome_image" ></span>';
                    }

                })
                ->addColumn('warehouse_id', function ($row) {
                    return 'warehouse 1';
                })
                ->addColumn('quantity', function ($row) {
                    return $row->getQuantity->quantity;
                })
                ->addColumn('manage', function ($row) {
                    return '<span x-on:click=" showEditModal=true"class="modal-open bg-green-500 text-white rounded px-2 py-1 mr-1 capitalize cursor-pointer" data-id="' . $row->id . '" id="BtnUpdate">In/Out</span> <span class="bg-blue-500 text-white rounded px-2 py-1 mr-4 capitalize cursor-pointer" "' . $row->id . '" id=""><a href="'. url("staff/see-details/$row->id") .'"><i class="fas fa-eye"></i></a></span>';
                })

                ->rawColumns(['image','manage'])
                ->make(true);
            return $table;
        }
    }

    public function productUpdate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'check' => 'required',
            'quantity' => 'required|numeric'
        ],);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $user = Auth::user();
        if($request->quantity > 0)
        {
            $data = ProductMainStock::where('product_id',$request->id)->first();
            if($request->check == 1)
            {
                $quantity = $data->quantity + $request->quantity;
                $data->quantity = $quantity;
                $result = $data->save();

                $stockLog = new StockLog();
                $stockLog->product_id = $request->id;
                $stockLog->quantity = $request->quantity;
                if (Auth::user()->role == 'staff')
                    $stockLog->staff_id = Auth::user()->staff_id;
                $stockLog->seller_id = Auth::user()->id;
                $stockLog->date = date('Y-m-d',strtotime('today'));
                $stockLog->check_in_out = 1;
                $stockLog->save();

                if ($result) {
                    return redirect()->back()->with('success', 'Product quantity successfully checked in');
                } else {
                    return redirect()->back()->with('error', 'Wrong Password');
                }
            }
            else{
                if($data->quantity < $request->quantity){
                    return redirect()->back()->with('danger', 'After CheckOut this quantity. It become less then zero. Please Insert valide quantity value');
                }
                else
                {
                    $quantity = $data->quantity - $request->quantity;
                    $data->quantity = $quantity;
                    $result = $data->save();

                    $stockLog = new StockLog();
                    $stockLog->product_id = $request->id;
                    $stockLog->quantity = $request->quantity;
                    if (Auth::user()->role == 'staff')
                        $stockLog->staff_id = Auth::user()->staff_id;
                    $stockLog->seller_id = Auth::user()->id;
                    $stockLog->date = date('Y-m-d',strtotime('today'));
                    $stockLog->check_in_out = 0;
                    $stockLog->save();
                    if ($result) {
                        return redirect()->back()->with('success', 'Product quantity successfully checked out');
                    } else {
                        return redirect()->back()->with('error', 'Wrong Password');
                    }
                }
            }
        }
        return redirect()->back()->with('danger', 'Number must be greater than zero');
    }

    public function seeDetails($id)
    {
        $user = Auth::user();
        $quantityLogs = StockLog::where('product_id',$id)->with('seller')->get();
        $product = Product::find($id);
        return view('staff.qunatity-log-details', compact('quantityLogs','product'));
    }

}
