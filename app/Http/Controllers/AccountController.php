<?php

namespace App\Http\Controllers;

use App\Models\DropshipperAddress;
use App\Models\Package;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;

class AccountController extends Controller
{
    public function profile()
    {
        $dropshipperAddress = '';
        $user = Auth::user();

        if (Auth::user()->role == 'dropshipper') {
            $user = User::where(['id' => Auth()->user()->dropshipper_id])->first();
            $dropshipperAddress = DropshipperAddress::where('user_id', Auth()->user()->dropshipper_id)->first();
        }

        $availablePrefLang = User::getAllPrefLang();

        return view('seller.profile', compact('user', 'dropshipperAddress', 'availablePrefLang'));
    }

    public function changePassword(Request $request)
    {
        $input = $request->all();
        $validator = $this->validatePassword($input);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        } else {

            if (Auth::user()->role == 'staff')
                $user = DB::table('users')->where(['id' => Auth()->user()->staff_id])->first();
            elseif (Auth::user()->role == 'dropshipper')
                $user = DB::table('users')->where(['id' => Auth()->user()->dropshipper_id])->first();
            else
                $user = DB::table('users')->where(['id' => Auth()->user()->id])->first();

            $currentPassword = $user->password;

            // validate if inputed password same with current password
            if (Hash::check($input['current-password'], $currentPassword)) {

                DB::table('users')->where('id', $user->id)
                    ->update([
                        'password' => Hash::make($input['new-password'])
                    ]);
                return redirect()->back()->with('success', 'Password successfully changed');
            } else {
                return redirect()->back()->with('error', 'Wrong Password');
            }
        }
    }

    public function validatePassword(array $input)
    {

        // validating user input
        $rules = [
            'current-password' => 'required|string',
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

    public function profileUpdate(Request $request)
    {
        $request->validate([
            // 'shop_id' => 'required|unique:users,shop_id|max:4|alpha',
            'name' => 'required|string|max:255',
            'contactname' => 'required|string|max:255',
            'phone' => 'required|numeric',
            //'lineid' => 'required|string|max:25',
            'email' => 'required|string|email|max:255',
        ]);

        $user = User::find(Auth::user()->id);
        $user->name = $request->name;
        $user->contactname = $request->contactname;
        $user->phone = $request->phone;
        $user->lineid = $request->lineid;
        $user->email = $request->email;

            if ($request->hasFile('logo')) {
                $upload = $request->file('logo');
                $file_type = $upload->getClientOriginalExtension();
                $upload_name =  time() . $upload->getClientOriginalName();
                $destinationPath = public_path('uploads/user_logo');
                $upload->move($destinationPath, $upload_name);
                $user->logo = 'uploads/user_logo/'.$upload_name;
            }

        $result = $user->save();
        if ($result) {

            return redirect()->back()->with('success', 'Profile update succesfully');
        } else {
            return redirect()->back()->with('error', 'Wrong Password');
        }
    }

    public function yourPackages(){
        $packages = Package::all();

        return view('seller.packages',compact('packages'));
    }
}
