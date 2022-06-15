<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Customer;
use App\Models\DropshipperAddress;
use App\Models\OrderManagement;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class DropshipperController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $data  = User::where('role','dropshipper')->get();
        $title = 'dropshippers';
        return view('dropshipper.index', compact('data', 'title'));
    }

    public function data(Request $request)
    {
        if ($request->ajax()) {

            if (isset($request->id) && $request->id != null) {
                $editData = User::where([
                    'id' => $request->id
                ])->first();
                $id = $request->id;
                $roles = Role::where('user_type', 1)->get();
                $shops = Shop::all();
                $customers = Customer::all();

                return view('elements.form-update-dropshipper', compact(['editData', 'id', 'roles', 'shops', 'customers']));
            }

            $data = User::where('role', 'dropshipper')
                ->where('seller_id', Auth::user()->id)
                ->orderBy('id', 'desc')
                ->get();

            $table = Datatables::of($data)
                ->addColumn('logo', function ($row) {
                    if(!empty($row->logo) && file_exists(public_path($row->logo))) {
                        return '<span><img src="'.asset($row->logo).'" style="width:60px;height:55px"></span>';
                    }
                    else
                        return '<span><img src="'.asset('No-Image-Found.png').'" style="width:60px;height:55px"></span>';
                })
                ->addColumn('created_at', function ($row) {
                    return $row->created_at->format('d-m-Y H:i');
                })
                ->addColumn('dropshipper_role', function ($row) {
                    return $row->getRoleNames()->first();
                })
                ->addColumn('address_detail', function ($row) {
                    return  $row->dropshipperAddress->address . ' <br> '
                        . $row->dropshipperAddress->sub_district . ', '
                        . $row->dropshipperAddress->district . ' <br> '
                        . $row->dropshipperAddress->province . ' '
                        . $row->dropshipperAddress->postcode;
                })
                ->addColumn('manage', function ($row) {
                    if (Auth::user()->role == 'member'){
                        return '<div class="w-full text-center">
                                    <button type="button" class="modal-open btn-action--green" title="'. __('translation.Edit') .'" x-on:click="showEditModal=true" data-id="' . $row->id . '" id="BtnUpdate">
                                       <i class="fas fa-pencil-alt"></i>
                                    </button>
                                    <button type="button" class="btn-action--red" title="'. __('translation.Delete') .'" data-id="' . $row->id . '" id="BtnDelete">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                    <button type="button" class="modal-open btn-action--yellow" title="'. __('translation.Change Password') .'" title="" data-id="' . $row->id . '" id="BtnPasswordChange">
                                        <i class="fas fa-key"></i>
                                    </button>
                                    <a href="'.route('dropshipper.assign_permission.user', [ 'id' => $row->id ]) .'" class="btn-action--gray" title="'. __('translation.Assign Permission') .'">
                                        <i class="fas fa-arrow-circle-right"></i>
                                    </a>
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
                ->rawColumns(['logo', 'created_at', 'dropshipper_role', 'address_detail', 'manage'])
                ->make(true);
            return $table;
        }
    }


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $roles = Role::where('user_type', 1)->get();
        $shops = Shop::all();
        $customers = Customer::all();

        return view('elements.form-update-dropshipper', compact('roles', 'shops', 'customers'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'shop_id' => 'required',
            'customer_id' => 'required',
            'email' => 'required|email|unique:users',
            'phone' => 'required|unique:users|numeric',
            'password' => 'required|string|min:8',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $shop = Shop::findOrFail($request->shop_id);
        $customer = Customer::findOrFail($request->customer_id);

        $user = new User();
        $user->shop_id = $request->shop_id;
        $user->customer_id = $request->customer_id;
        $user->name = $shop->name;
        $user->contactname = $request->contactname;
        $user->email = $request->email;
        $user->phone = $request->phone;
        $user->role = 'dropshipper';
        $user->seller_id = Auth::user()->id;
        $user->password = Hash::make($request->password);
        $user->is_active = 1;

        if ($request->hasFile('logo')) {
            $upload = $request->file('logo');
            $upload_name =  time() . $upload->getClientOriginalName();
            $destinationPath = public_path('uploads/user_logo');
            $upload->move($destinationPath, $upload_name);
            $user->logo = 'uploads/user_logo/'.$upload_name;
        }
        $user->save();

        if (!empty($request->full_address)) {
            $full_address = $request->full_address;
            $nameArr = explode('/', $full_address);

            $dropshipper_address = new DropshipperAddress();
            $dropshipper_address->user_id = $user->id;
            $dropshipper_address->address = $request->address;
            $dropshipper_address->district = $nameArr[0];
            $dropshipper_address->sub_district = $nameArr[1];
            $dropshipper_address->province = $nameArr[2];
            $dropshipper_address->postcode = $nameArr[3];
            $dropshipper_address->save();
        }

        $customer->is_dropshipper = 1;
        $customer->save();

        if ($request->role) {
            $dropshipper_role = Role::find($request->role);
            $user->roles()->attach($dropshipper_role);
        }

        if($user)
        {
            return redirect()->back()->with('success','Dropshipper successfully created.');
        }
        else{
            return redirect()->back()->with('danger','Something went wrong.');
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
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required',
            'shop_id' => 'required',
            'customer_id' => 'required',
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $shop = Shop::findOrFail($request->shop_id);
        $customer = Customer::findOrFail($request->customer_id);

        $user = User::where('id', $request->id)->first();
        $user->customer_id = $request->customer_id;
        $user->shop_id = $request->shop_id;
        $user->name = $shop->name;
        $user->contactname = $request->contactname;
        $user->email = $request->email;
        $user->phone = $request->phone;

        if ($request->hasFile('logo')) {
            $upload = $request->file('logo');
            $upload_name =  time() . $upload->getClientOriginalName();
            $destinationPath = public_path('uploads/user_logo');
            $upload->move($destinationPath, $upload_name);
            $user->logo = 'uploads/user_logo/'.$upload_name;
        }

        if (!empty($request->full_address)) {
            if ($user->dropshipperAddress->id){
                $dropshipper_address = DropshipperAddress::find($user->dropshipperAddress->id);
            }
            else{
                $dropshipper_address = new DropshipperAddress();
                $dropshipper_address->user_id = $user->id;
            }
            $full_address = $request->full_address;
            $nameArr = explode('/', $full_address);
            $dropshipper_address->address = $request->address;
            $dropshipper_address->district = $nameArr[0];
            $dropshipper_address->sub_district = $nameArr[1];
            $dropshipper_address->province = $nameArr[2];
            $dropshipper_address->postcode = $nameArr[3];
            $dropshipper_address->save();
        }

        $result = $user->update();

        $customer->is_dropshipper = 1;
        $customer->update();

        if ($request->role) {
            $dropshipper_role = Role::find($request->role);
            $user->roles()->detach();
            $user->roles()->attach($dropshipper_role);
        }

        if($result)
        {
            return redirect()->back()->with('success','Data successfully updated.');
        }
        else{
            return redirect()->back()->with('danger','Something went wrong.');
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
        }

        $user = User::where('id', $request->id)->first();
        if(file_exists($user->logo)){
            unlink($user->logo);
        }
        $user->delete();

        return [
            'status' => 1
        ];
    }


//    -------------------------
//    --   DROPSHIPPER ROLE  --
//    -------------------------

    public function dropshipperRole()
    {
        $roles  = Role::where('user_type', 1)->get();
        $title = 'dropshipper role';
        return view('dropshipper.roles', compact('roles', 'title'));
    }

    public function dropshipperData(Request $request)
    {
        if ($request->ajax()) {

            if (isset($request->id) && $request->id != null) {
                $data = Role::where([
                    'id' => $request->id
                ])->first();

                $id = $request->id;

                return view('elements.form-update-role', compact(['data', 'id']));
            }

            $data  = Role::where('user_type', 1)->get();

            $table = Datatables::of($data)
                ->addColumn('action', function ($row) {
                    return '
                            <div class="w-full text-center">
                                <button type="button" class="modal-open btn-action--green" title="'. __('translation.Edit') .'" x-on:click="showEditModal=true" data-id="' . $row->id . '" id="BtnUpdate">
                                    <i class="fas fa-pencil-alt"></i>
                                </button>
                                <button type="button" class="btn-action--red" title="'. __('translation.Delete') .'" data-id="' . $row->id . '" id="BtnDelete">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                                <a href="'.route('dropshipper.assign_permission.role', [ 'id' => $row->id ]) .'" class="btn-action--gray" title="'. __('translation.Assign Permission') .'">
                                    <i class="fas fa-arrow-circle-right"></i>
                                </a>
                            </div>';
                })
                ->rawColumns(['action'])
                ->make(true);
            return $table;
        }
    }

//    DROPSHIPPER ASSIGN PERMISSION BY ROLE

    public function dropshipperPermissionByRole($id)
    {
        $role = Role::findOrFail($id);
        $categories = Category::where('seller_id',Auth::user()->id)->where('parent_category_id', 0)->get();
        $sub_categories = Category::where('seller_id',Auth::user()->id)->where('parent_category_id', '!=', 0)->get();
        $title = 'dropshipper role';

        $totalPermissionCount = Permission::where('user_type', 1)->whereHas('product')->count();
        $selectedPermissionCount = Permission::where('user_type', 1)->whereHas('product')
            ->whereHas('roles', function ($query) use ($id) {
                return $query->where('roles.id', $id);
            })->count();
        $unselectedPermissionCount = Permission::where('user_type', 1)->whereHas('product')
            ->whereDoesntHave('roles', function ($query) use ($id) {
                return $query->where('roles.id', $id);
            })->count();

        $data = [
            'role' => $role,
            'categories' => $categories,
            'sub_categories' => $sub_categories,
            'totalPermissionCount' => $totalPermissionCount,
            'selectedPermissionCount' => $selectedPermissionCount,
            'unselectedPermissionCount' => $unselectedPermissionCount,
            'title' => $title
        ];

        return view('dropshipper.assign-role-permission', $data);
    }

    public function dataDropshipperPermissionByRole(Request $request)
    {
        $data = [];

        $roleId = $request->get('roleId', 0);
        $rolePermissions = DB::table("roles_permissions")->where("role_id", $roleId)
            ->pluck('permission_id','permission_id')
            ->all();

        $categoryId = $request->get('categoryId', '');
        $sortBySelected = $request->get('sortBySelected', '');

        $start = $request->get('start', 0);
        $limit = $request->get('length', 10);

        $search = isset($request->get('search')['value'])
            ? $request->get('search')['value']
            : null;

        $orderColumnIndex = isset($request->get('order')[0]['column'])
            ? $request->get('order')[0]['column']
            : 2;
        $orderDir = isset($request->get('order')[0]['dir'])
            ? $request->get('order')[0]['dir']
            : 'desc';

        $availableColumnsOrder = [
            'id', 'id', 'product_name', 'price', 'dropship_price', 'quantity'
        ];

        $orderColumnName = $availableColumnsOrder[$orderColumnIndex] ?? $availableColumnsOrder[1];

        if ($sortBySelected == 'selected'){
            $fields = Permission::where('user_type', 1)
                ->whereHas('roles', function ($query) use ($roleId) {
                    return $roleId ? $query->where('roles.id', $roleId) : '';
                })
                ->whereHas('product', function ($query) use ($categoryId) {
                    return $categoryId ? $query->where('products.category_id', $categoryId) : '';
                })
                ->searchDataTable($search)
                ->productNameAsColumn()
                ->productPriceAsColumn()
                ->productDropshipPriceAsColumn()
                ->quantity()
                ->orderBy($orderColumnName, $orderDir)
                ->take($limit)
                ->skip($start)
                ->get();

            $count_total = Permission::where('user_type', 1)
                ->whereHas('roles', function ($query) use ($roleId) {
                    return $roleId ? $query->where('roles.id', $roleId) : '';
                })
                ->whereHas('product', function ($query) use ($categoryId) {
                    return $categoryId ? $query->where('products.category_id', $categoryId) : '';
                })
                ->count();
            $count_total_search = Permission::where('user_type', 1)
                ->whereHas('roles', function ($query) use ($roleId) {
                    return $roleId ? $query->where('roles.id', $roleId) : '';
                })
                ->whereHas('product', function ($query) use ($categoryId) {
                    return $categoryId ? $query->where('products.category_id', $categoryId) : '';
                })
                ->searchDataTable($search)->count();

        }
        elseif ($sortBySelected == 'unselected'){
            $fields = Permission::where('user_type', 1)
                ->whereDoesntHave('roles', function ($query) use ($roleId) {
                    return $query->where('roles.id', $roleId);
                })
                ->whereHas('product', function ($query) use ($categoryId) {
                    return $categoryId ? $query->where('products.category_id', $categoryId) : '';
                })
                ->searchDataTable($search)
                ->productNameAsColumn()
                ->productPriceAsColumn()
                ->productDropshipPriceAsColumn()
                ->quantity()
                ->orderBy($orderColumnName, $orderDir)
                ->take($limit)
                ->skip($start)
                ->get();

            $count_total = Permission::where('user_type', 1)
                ->whereDoesntHave('roles', function ($query) use ($roleId) {
                    return $query->where('roles.id', $roleId);
                })
                ->whereHas('product', function ($query) use ($categoryId) {
                    return $categoryId ? $query->where('products.category_id', $categoryId) : '';
                })
                ->count();
            $count_total_search = Permission::where('user_type', 1)
                ->whereDoesntHave('roles', function ($query) use ($roleId) {
                    return $query->where('roles.id', $roleId);
                })
                ->whereHas('product', function ($query) use ($categoryId) {
                    return $categoryId ? $query->where('products.category_id', $categoryId) : '';
                })
                ->searchDataTable($search)->count();
        }
        else {
            $fields = Permission::where('user_type', 1)
                ->whereHas('product', function ($query) use ($categoryId) {
                    return $categoryId ? $query->where('products.category_id', $categoryId) : '';
                })
                ->searchDataTable($search)
                ->productNameAsColumn()
                ->productPriceAsColumn()
                ->productDropshipPriceAsColumn()
                ->quantity()
                ->orderBy($orderColumnName, $orderDir)
                ->take($limit)
                ->skip($start)
                ->get();

            $count_total = Permission::where('user_type', 1)
                ->whereHas('product', function ($query) use ($categoryId) {
                    return $categoryId ? $query->where('products.category_id', $categoryId) : '';
                })
                ->count();
            $count_total_search = Permission::where('user_type', 1)
                ->whereHas('product', function ($query) use ($categoryId) {
                    return $categoryId ? $query->where('products.category_id', $categoryId) : '';
                })
                ->searchDataTable($search)->count();
        }

        if (!empty($fields)) {
            foreach ($fields as $field) {
                $row = [];

                $checked = '';
                if (in_array($field->id, $rolePermissions))
                    $checked = 'checked';

                $checkBox = '<input type="checkbox" class="dt-checkboxes permission" name="permission[]" id="'. $field->id .'" ' . $checked .'>';

                $row[] = $field->id;

                if (!empty($field->product->image) && file_exists(public_path($field->product->image)))
                    $imageContent = '<img src="'. asset($field->product->image) .'" width="75px" class="65px">
                                    <div>
                                        <span class="text-blue-500 font-bold">
                                            ID: '. $field->product->id .'
                                        </span>
                                    </div>';
                else
                    $imageContent = '<img src="'. asset('No-Image-Found.png') .'" width="75px" class="65px">
                                    <div>
                                        <span class="text-blue-500 font-bold">
                                            ID: '. $field->product->id .'
                                        </span>
                                    </div>';

                $row[] = $imageContent;

                $product = '<span class="hide">
                                '. $checked .'
                            </span>
                            <span class="font-bold">
                                '. $field->product->product_name .'
                            </span><br>
                            <span class="text-blue-500 font-bold">
                                '. $field->product->product_code .'
                            </span>';
                $row[] = $product;

                $row[] = currency_symbol('THB') . ' ' .$field->product->price;
                $row[] = currency_symbol('THB') . ' ' . number_format($field->product->dropship_price) ;
                $row[] = number_format($field->quantity);

                $data[] = $row;
            }
        }

        $response = [
            'draw' => $request->get('draw'),
            'recordsTotal' => $count_total,
            'recordsFiltered' => $count_total_search,
            'data' => $data
        ];

        return response()->json($response);
    }

    public function dropshipperAssignPermissionByRole(Request $request)
    {
        if (isset($request->roleId) && isset($request->permission) && isset($request->action)) {
            $role = Role::find($request->roleId);

            if ($request->action == 'attach') {

                $role->permissions()->detach($request->permission);
                $role->permissions()->attach($request->permission);

                return [
                    'status' => 1
                ];
            }
            else if ($request->action == 'detach') {

                $role->permissions()->detach($request->permission);

                return [
                    'status' => 1
                ];
            }
        }
    }

//    DROPSHIPPER ASSIGN PERMISSION BY USER

    public function dropshipperPermissionByUser($id)
    {
        $user = User::findOrFail($id);
        $categories = Category::where('seller_id',Auth::user()->id)->where('parent_category_id', 0)->get();
        $sub_categories = Category::where('seller_id',Auth::user()->id)->where('parent_category_id', '!=', 0)->get();

        $totalPermissionCount = Permission::where('user_type', 1)->whereHas('product')->count();
        $selectedPermissionCount = $user->getAllPermissions()->count();

        $title = 'dropshippers';

        $data = [
            'user' => $user,
            'categories' => $categories,
            'sub_categories' => $sub_categories,
            'totalPermissionCount' => $totalPermissionCount,
            'selectedPermissionCount' => $selectedPermissionCount,
            'title' => $title
        ];

        return view('dropshipper.assign-user-permission', $data);
    }

    public function dataDropshipperPermissionByUser(Request $request)
    {
        $data = [];

        $userId = $request->get('userId', 0);
        $user = User::findOrFail($userId);
        $roleId = $user->getRoleId()->first();
        $permissions = $user->getAllPermissions()->toArray();
        $userPermissions = [];
        foreach ($permissions as $key=>$value){
            $userPermissions[] = $value['id'];
        }

        $categoryId = $request->get('categoryId', '');
        $sortBySelected = $request->get('sortBySelected', '');

        $start = $request->get('start', 0);
        $limit = $request->get('length', 10);

        $search = isset($request->get('search')['value'])
            ? $request->get('search')['value']
            : null;

        $orderColumnIndex = isset($request->get('order')[0]['column'])
            ? $request->get('order')[0]['column']
            : 2;
        $orderDir = isset($request->get('order')[0]['dir'])
            ? $request->get('order')[0]['dir']
            : 'desc';

        $availableColumnsOrder = [
            'id', 'id', 'product_name', 'price', 'dropship_price', 'quantity'
        ];

        $orderColumnName = $availableColumnsOrder[$orderColumnIndex] ?? $availableColumnsOrder[1];

        if ($sortBySelected == 'selected'){
            $fields = Permission::where('user_type', 1)
                ->whereHas('users', function ($query) use ($userId) {
                    return $userId ? $query->where('users.id', $userId) : '';
                })
                ->orWhereHas('roles', function ($query) use ($roleId) {
                    return $roleId ? $query->where('roles.id', $roleId) : '';
                })
                ->whereHas('product', function ($query) use ($categoryId) {
                    return $categoryId ? $query->where('products.category_id', $categoryId) : '';
                })
                ->searchDataTable($search)
                ->productNameAsColumn()
                ->productPriceAsColumn()
                ->productDropshipPriceAsColumn()
                ->quantity()
                ->orderBy($orderColumnName, $orderDir)
                ->take($limit)
                ->skip($start)
                ->get();

            $count_total = Permission::where('user_type', 1)
                ->whereHas('users', function ($query) use ($userId) {
                    return $userId ? $query->where('users.id', $userId) : '';
                })
                ->orWhereHas('roles', function ($query) use ($roleId) {
                    return $roleId ? $query->where('roles.id', $roleId) : '';
                })
                ->whereHas('product', function ($query) use ($categoryId) {
                    return $categoryId ? $query->where('products.category_id', $categoryId) : '';
                })
                ->count();
            $count_total_search = Permission::where('user_type', 1)
                ->whereHas('users', function ($query) use ($userId) {
                    return $userId ? $query->where('users.id', $userId) : '';
                })
                ->orWhereHas('roles', function ($query) use ($roleId) {
                    return $roleId ? $query->where('roles.id', $roleId) : '';
                })
                ->whereHas('product', function ($query) use ($categoryId) {
                    return $categoryId ? $query->where('products.category_id', $categoryId) : '';
                })
                ->searchDataTable($search)->count();

        }
        elseif ($sortBySelected == 'unselected'){
            $fields = Permission::where('user_type', 1)
                ->whereDoesntHave('users', function ($query) use ($userId) {
                    return $query->where('users.id', $userId);
                })
                ->whereDoesntHave('roles', function ($query) use ($roleId) {
                    return $query->where('roles.id', $roleId);
                })
                ->whereHas('product', function ($query) use ($categoryId) {
                    return $categoryId ? $query->where('products.category_id', $categoryId) : '';
                })
                ->searchDataTable($search)
                ->productNameAsColumn()
                ->productPriceAsColumn()
                ->productDropshipPriceAsColumn()
                ->quantity()
                ->orderBy($orderColumnName, $orderDir)
                ->take($limit)
                ->skip($start)
                ->get();

            $count_total = Permission::where('user_type', 1)
                ->whereDoesntHave('users', function ($query) use ($userId) {
                    return $query->where('users.id', $userId);
                })
                ->whereDoesntHave('roles', function ($query) use ($roleId) {
                    return $query->where('roles.id', $roleId);
                })
                ->whereHas('product', function ($query) use ($categoryId) {
                    return $categoryId ? $query->where('products.category_id', $categoryId) : '';
                })
                ->count();
            $count_total_search = Permission::where('user_type', 1)
                ->whereDoesntHave('users', function ($query) use ($userId) {
                    return $query->where('users.id', $userId);
                })
                ->whereDoesntHave('roles', function ($query) use ($roleId) {
                    return $query->where('roles.id', $roleId);
                })
                ->whereHas('product', function ($query) use ($categoryId) {
                    return $categoryId ? $query->where('products.category_id', $categoryId) : '';
                })
                ->searchDataTable($search)->count();
        }
        else {
            $fields = Permission::where('user_type', 1)
                ->whereHas('product', function ($query) use ($categoryId) {
                    return $categoryId ? $query->where('products.category_id', $categoryId) : '';
                })
                ->searchDataTable($search)
                ->productNameAsColumn()
                ->productPriceAsColumn()
                ->productDropshipPriceAsColumn()
                ->quantity()
                ->orderBy($orderColumnName, $orderDir)
                ->take($limit)
                ->skip($start)
                ->get();

            $count_total = Permission::where('user_type', 1)
                ->whereHas('product', function ($query) use ($categoryId) {
                    return $categoryId ? $query->where('products.category_id', $categoryId) : '';
                })
                ->count();
            $count_total_search = Permission::where('user_type', 1)
                ->whereHas('product', function ($query) use ($categoryId) {
                    return $categoryId ? $query->where('products.category_id', $categoryId) : '';
                })
                ->searchDataTable($search)->count();
        }

        if (!empty($fields)) {
            foreach ($fields as $field) {
                $row = [];

                $checked = '';
                if (in_array($field->id, $userPermissions))
                    $checked = 'checked';

                $row[] = $field->id;

                if (!empty($field->product->image) && file_exists(public_path($field->product->image)))
                    $imageContent = '<img src="'. asset($field->product->image) .'" width="75px" class="65px">
                                    <div>
                                        <span class="text-blue-500 font-bold">
                                            ID: '. $field->product->id .'
                                        </span>
                                    </div>';
                else
                    $imageContent = '<img src="'. asset('No-Image-Found.png') .'" width="75px" class="65px">
                                    <div>
                                        <span class="text-blue-500 font-bold">
                                            ID: '. $field->product->id .'
                                        </span>
                                    </div>';

                $row[] = $imageContent;

                $product = '<span class="hide">
                                '. $checked .'
                            </span>
                            <span class="font-bold">
                                '. $field->product->product_name .'
                            </span><br>
                            <span class="text-blue-500 font-bold">
                                '. $field->product->product_code .'
                            </span>';
                $row[] = $product;

                $row[] = currency_symbol('THB') . ' ' .$field->product->price;
                $row[] = currency_symbol('THB') . ' ' . number_format($field->product->dropship_price) ;
                $row[] = number_format($field->quantity);

                $data[] = $row;
            }
        }

        $response = [
            'draw' => $request->get('draw'),
            'recordsTotal' => $count_total,
            'recordsFiltered' => $count_total_search,
            'data' => $data
        ];

        return response()->json($response);
    }

    public function dropshipperAssignPermissionByUser(Request $request)
    {
        if (isset($request->userId) && isset($request->permission) && isset($request->action)) {
            $user = User::find($request->userId);

            if ($request->action == 'attach') {

                $user->permissions()->detach($request->permission);
                $user->permissions()->attach($request->permission);

                return [
                    'status' => 1
                ];
            }
            else if ($request->action == 'detach') {

                $user->permissions()->detach($request->permission);

                return [
                    'status' => 1
                ];
            }
        }
    }


//    -----------------------------
//    --   DROPSHIPPER OVERVIEW  --
//    -----------------------------

    public function dropshipperOrders()
    {
        $data  = User::where('role','dropshipper')->get();
        $title = 'dropshipper orders';
        return view('dropshipper.overview', compact('data', 'title'));
    }

    public function dropshipperOrdersData(Request $request)
    {
        if ($request->ajax()) {

            $data = User::where('role', 'dropshipper')
                ->where('seller_id', Auth::user()->id)
                ->totalOrders()
                ->totalOrdersAmount()
                ->orderBy('id', 'desc')
                ->get();

            $table = Datatables::of($data)
                ->addColumn('shop_name', function ($row) {
                    return $row->shop->name;
                })
                ->addColumn('dropshipper_role', function ($row) {
                    return $row->getRoleNames()->first();
                })
                ->addColumn('action', function ($row) {
                    return '<div class="w-full text-center">
                                <a href="'.route('customer.order_list', [ 'id' => $row->customer_id ]) .'" class="btn-action--blue" title="'. __('translation.Order List') .'">
                                    &nbsp;<i class="fab fa-buffer"></i>&nbsp;
                                </a>
                            </div>';
                })
                ->rawColumns(['shop_name', 'dropshipper_role', 'action'])
                ->make(true);
            return $table;
        }
    }


}
