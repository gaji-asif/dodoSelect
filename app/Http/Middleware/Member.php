<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Auth;

class Member
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if (Auth::user()->role == 'admin') {
            return redirect('admin/dashboard');
        }


        // $user = Auth::user();
        // $userRole = $user->role;

        // $asd = request()->getHttpHost();
        // $asdd = explode('.', $asd);

        // if (count($asdd) == 4) {
        //     $getCompany_name = $asdd[0];

        //     $company_name =  $company_name = str_replace('_', ' ', $getCompany_name);

        //     $company = User::where('username', $company_name)->where('is_active', 1)->first();

        //     $getCompanyId = $user->id;
        //     if (isset($company) && ($company->id == $getCompanyId)) {
        //         if (Auth::user()->role == 'member') {
        //             return $next($request);
        //         }
        //         abort(403);
        //     }
        //     abort(403);
        // } else if (count($asdd) == 3) {

        //     if (Auth::user()->role == 'member') {

        //         $company_name = 'hello';
        //         if (isset($user->username)) {
        //             $company_name = str_replace(' ', '_', $user->username);
        //         }

        //         $url = 'http://' . $company_name . '.shaheedrafiqmkj.edu.bd/dodotracking/public/signin';

        //         return redirect()->intended($url);
        //     }
        // }

        if (Auth::user()->role == 'member' || Auth::user()->role == 'staff' ) {
            return $next($request);
        }
    }
}
