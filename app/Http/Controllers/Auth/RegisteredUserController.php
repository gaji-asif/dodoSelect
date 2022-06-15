<?php

namespace App\Http\Controllers\Auth;

use App\Actions\SendOtp;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Tzsk\Otp\Facades\Otp;
use Session;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @param  RegisterRequest $request
     * @return \Illuminate\Http\RedirectResponse
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(RegisterRequest $request)
    {
        $uniqueSecret = time();
        $otp = Otp::generate($uniqueSecret);

        Session::put('user_phone', $request->phone);

        $thaiBulkResponse = SendOtp::make()->handle($request->phone, $otp);

        if (isset($thaiBulkResponse->error)) {
            throw new ThaiBulkSmsException($thaiBulkResponse->error->name, $thaiBulkResponse->error->code);

            if ($thaiBulkResponse->error->name == ErrorResponse::ERROR_MSISDN) {
                return redirect()->back()->with('failed', __('translation.Your mobile number is invalid'));
            }

            return redirect()->back()->with('failed', __('translation.Something went wrong'));
        }

        $user = new User();
        $user->name = $request->name;
        $user->username = $request->username;
        $user->contactname = $request->contactname;
        $user->phone = $request->phone;
        $user->lineid = $request->lineid;
        $user->email = $request->email;
        $user->role = 'member';
        $user->otp = $otp;
        $user->is_active = 0;
        $user->password = Hash::make($request->password);
        $user->save();

        $message = __('translation.We have sent the OTP');
        return redirect()->route('verify_mobile')->with('success', $message);
    }
}
