<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\Permission;
use App\Models\Product;
use App\Models\Role;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('auth.signin');
    }

    /**
     * Handle an incoming authentication request.
     *
     * @param  \App\Http\Requests\Auth\LoginRequest  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(LoginRequest $request)
    {
        $request->authenticate();

        $request->session()->regenerate();

        if ((bool)Auth::user()->is_active) {

            if (Auth::user()->role == 'staff') {
                $staff_id = Auth::user()->staff_id;
                $role_id = DB::table('users_roles')->where('user_id', $staff_id)->pluck('role_id')->first();
                $role = Role::find($role_id);
                if ($role) {
                    $permissions = $role->getPermissionNames()->toArray();
                    Session::put('assignedPermissions', $permissions);
                    Session::put('staff_role', $role);
                }
                else{
                    Session::put('staff_role', 'no role');
                    return redirect()->route('no_role_profile');
                }
                Session::put('roleName', 'staff');
            }

            elseif (Auth::user()->role == 'dropshipper') {
                $permissions = ['Can access menu: Order Management', 'Can access menu: Product'];
                Session::put('assignedPermissions', $permissions);
                Session::put('roleName', 'dropshipper');
                return redirect()->route('order_management.index');
            }

            else{
                $permissions = Permission::where('user_type', 0)->pluck('name')->all();
                Session::put('assignedPermissions',$permissions);
                Session::put('roleName', 'member');
            }


            if (Auth::user()->role == 'admin') {
                return redirect('admin/dashboard');
            }
            // if (Auth::user()->role == 'staff') {
            //     return redirect('staff/dashboard');
            // }

            // $company_name = 'hello';
            // if (isset(Auth::user()->username)) {
            //     $company_name = str_replace(' ', '_', Auth::user()->username);
            // }
            // $url = 'http://' . $company_name . '.shaheedrafiqmkj.edu.bd/dodotracking/public/dashboard';
            // return redirect()->intended($url);

            return redirect()->intended(RouteServiceProvider::HOME)->with('registration-success','Welcome to DodoSelect');

        }

        Auth::logout();

        return redirect()->back()->with('failed', 'Your account was suspended');
    }

    /**
     * Destroy an authenticated session.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Request $request)
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/signin');
        //    return redirect('http://shaheedrafiqmkj.edu.bd/dodotracking/public/signin');
    }
}
