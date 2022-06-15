<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Session;
use App\Models\User;
use Tzsk\Otp\Facades\Otp;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use App\SMS_ClASS\SMS;


class OptController extends Controller
{
    public function verifyMobile()
    {

        return view('otp.verifyOtp');
    }

    public function forgetPassword()
    {
        return view('otp.enterPhoneNumber');
    }

    public function resetPassword()
    {
        return view('otp.resetPassword');
    }

    public function getOtp(Request $request)
    {
        if($request->for_what == 1)
        {
            $mobile = session::get('user_phone');
            session::forget('user_phone');
        }
        else
        {
            $mobile = session::get('user_phone1');
            session::forget('user_phone1');
        }
        $user = User::where('phone',$mobile)->first();
        $unique_secret = 'moinuddin';
            if($user->otp == $request->otp)
            {
                if($valid = Otp::match($request->otp, $unique_secret))
                {
                    if($request->for_what == 1)
                    {
                        $user->is_active = 1;
                        $user->save();
                        return redirect('/signin');
                    }
                    else{
                        Session::put('user_id',$user->id);
                        return redirect('/reset_password');
                    }
                   
                }
                else{
                    return redirect()->back()->with('failed',"Otp expired");
                }
            }
            else{
                return redirect()->back()->with('failed',"This otp is invalid");
            }
    }

    public function getPhone(Request $request)
    {
        $request->validate([
            'phone' => 'required|numeric',
        ]);

        $user = User::where('phone',$request->phone)->first();

        if(!isset($user->phone))
        {
            return redirect()->back()->with('failed',"Unregistered Phone number");
        }
        
        if(isset($user->phone) && $user->is_active != 1 )
        {
            return redirect()->back()->with('failed',"Your account was suspended");
        }


        $unique_secret = 'moinuddin';
        $otp = Otp::generate($unique_secret);
        $user->otp = $otp;
        $user->save();
        $response = $this->sendOtp($request->phone,$otp);
        
        Session::put('user_phone1',$request->phone);
        if($response)
        {
            return redirect()->route('verify_mobile');
        }
        else
        {
            return redirect()->back()->with('failed',"Ops somethings happened");
        }

    }

    public function resetpass(Request $request)
    {
        $input = $request->all();
        $validator = $this->validatePassword($input);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        } else {

            $sessionUserId = session::get('user_id');
            session::forget('user_id');

            $user = User::find($sessionUserId);
            $user->password = Hash::make($input['password']);
            $result = $user->save();

            if ($result) {
                return redirect('/signin')->with('successs', 'Password succesfully Reseted');
            } else {
                return redirect()->back()->with('error', 'Wrong Password');
            }
        }
    }

    public function validatePassword(array $input)
    {

        // validating user input
        $rules = [
            'confirm_password' => 'required|string',
            'password' => 'required|string|min:8', //|,confirmed',
        ];
        $message = [
            // 'confirmed' => 'Password confirmation is wrong'
        ];
        $attributes = [
            'password' => 'New Password'
        ];
        $validator = Validator::make($input, $rules, $message, $attributes);

        return $validator;
    }

    public function sendOtp($mobile,$otp)
    {
        $apiKey = '02a756f62c6e6ba391f9680e48814361';
        $apiSecretKey = '0dba9623cacdbeab28c8a84510c1e1bf';
        
        $sms = new SMS($apiKey, $apiSecretKey);
        $body = [
            'msisdn' => $mobile,
            'message' => "Dodo tracking mobile verification. OTP : ".$otp,
            // 'sender' => '',
            // 'scheduled_delivery' => '',
            // 'force' => ''
        ];
       $res = $sms->sendSMS($body);
        
        if ($res->httpStatusCode == 201) {
            return true;
        } else {
          return false;
        }
    }
}
