<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Permission;
use App\Models\Product;
use App\Models\Role;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class RoleController extends Controller
{
//    -------------------
//    --   STAFF ROLE  --
//    -------------------
    public function staffRole()
    {
        $roles  = Role::where('user_type', 0)->get();
        $title = 'role';
        return view('seller.staff_roles.index', compact('roles', 'title'));
    }

    public function data(Request $request)
    {
        if ($request->ajax()) {

            if (isset($request->id) && $request->id != null) {
                $data = Role::where([
                    'id' => $request->id
                ])->first();

                $id = $request->id;

                return view('elements.form-update-role', compact(['data', 'id']));
            }

            $data  = Role::where('user_type', 0)->get();

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
                                <button type="button" class="modal-open btn-action--gray" title="'. __('translation.Assign Permission') .'" title="" data-id="' . $row->id . '" id="BtnAssign">
                                    <i class="fas fa-arrow-circle-right"></i>
                                </button>
                            </div>';
                })
                ->rawColumns(['action'])
                ->make(true);
            return $table;
        }
    }

    public function insert(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|min:4',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $role = Role::create([
            'name' => $request->name,
            'user_type' => $request->user_type,
            'description' => $request->description,
        ]);

        if ($role)
            return redirect()->back()->with('success', 'Role successfully created');
        else
            return redirect()->back()->with('danger', 'Role addition unsuccessful');
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $role = Role::find($request->id);
        $role->name = $request->name;
        $role->description = $request->description;
        $role->update();

        return redirect()->back()->with('success', 'Role successfully updated');
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

        Role::where('id', $request->id)->delete();
        return [
            'status' => 1
        ];
    }

//    STAFF PERMISSION

    public function permissions()
    {
        $permissions  = Permission::where('user_type', 0)->get();
        $title = 'permission';
        return view('seller.staff_roles.permissions', compact('permissions', 'title'));
    }

    public function dataPermission(Request $request)
    {
        if ($request->ajax()) {

            if (isset($request->id) && $request->id != null) {
                $data = Permission::where('id', $request->id)->first();
                $id = $request->id;

                return view('elements.form-update-staff-permission', compact(['data', 'id']));
            }

            $data = Permission::orderBy('name')->where('user_type', 0)->get();

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
                            </div>';
                })
                ->rawColumns(['action'])
                ->make(true);
            return $table;
        }
    }

    public function insertPermission(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|min:4',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $permission = Permission::create([
            'name' => $request->name,
            'user_type' => 0,
        ]);

        if ($permission)
            return redirect()->back()->with('success', 'Permission successfully created');
        else
            return redirect()->back()->with('danger', 'Permission addition unsuccessful');
    }

    public function updatePermission(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $permission = Permission::find($request->id);
        $permission->name = $request->name;
        $permission->update();

        return redirect('permissions')->with('success', 'Permission successfully updated');
    }

    public function deletePermission(Request $request)
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

        Permission::where('id', $request->id)->delete();
        return [
            'status' => 1
        ];
    }

//    STAFF ASSIGN PERMISSION

    public function assignPermission(Request $request)
    {
        $role = Role::find($request->id);
        $permission = Permission::orderBy('name')->where('user_type', 0)->get();
        $rolePermissions = DB::table("roles_permissions")->where("role_id",$request->id)
            ->pluck('permission_id','permission_id')
            ->all();
        $title = 'role';

        return view('seller.staff_roles.assign-permission', compact('role','permission','rolePermissions', 'title'));
    }

    public function saveAssignPermission(Request $request, $id)
    {
//        dd($request, $id);
        $this->validate($request, [
            'permission' => 'required',
        ]);

        $role = Role::find($id);

        $given_permission = [];

        if(isset($request->permission) && count($request->permission)>0){
            foreach ($request->permission as $item) {
                $given_permission[] = $item;
            }
        }

        $role->permissions()->detach();
        $role->permissions()->attach($given_permission);

        return redirect()->route('role')->with('success','Permissions has been assigned successfully');
    }

    public function noRole(){
        return view('seller.staff_roles.no-roles-page');
    }
}
