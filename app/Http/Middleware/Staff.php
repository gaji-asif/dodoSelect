<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Auth;

class Staff
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if (Auth::user()->role == 'admin') {
            return redirect('admin/dashboard');
        }

        if (Auth::user()->role == 'member') {
            return redirect('dashboard');
        }

        if (Auth::user()->role == 'staff') {
        
            //return $next($request);
            return redirect('dashboard');
        }
        
    }
}
