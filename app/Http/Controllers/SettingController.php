<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\DoDoChat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SettingController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth')->except('dodochat_users_logout');
    }

    public function index()
    {
        $categories = Category::where('parent_category_id',0)->where('seller_id',Auth::user()->id)->get();
        $data = Category::with('children')->where('seller_id',Auth::user()->id)->get();
        return view('settings.category',compact('categories','data'));
    }
    public function store(Request $request)
    {
       $category = new Category();
       $category->cat_name = $request->cat_name;
       if(empty($request->parent_category_id))
       {
            $category->parent_category_id = 0;
       }
       else
       {
            $category->parent_category_id = $request->parent_category_id;
       }

       $category->seller_id = Auth::user()->id;
       $category->save();

       if($category)
       {
           return redirect()->back()->with('success','Category Added Successfully');
       }
       else{
        return redirect()->back()->with('danger','Something happened wrong');
       }
    }
    public function edit($id)
    {
        $categories = Category::where('parent_category_id',0)->where('seller_id',Auth::user()->id)->get();
        $data = Category::with('children')->where('seller_id',Auth::user()->id)->get();
        $editData = Category::where('id',$id)->where('seller_id',Auth::user()->id)->first();

        return view('settings.category',compact('categories','data','editData'));
    }
    public function update(Request $request ,$id){
        $category = Category::find($id);
        $category->cat_name = $request->cat_name;
        if(!empty($request->parent_category_id))
        {
             $category->parent_category_id = $request->parent_category_id;
        }
        $category->save();

        if($category)
        {
            return redirect()->back()->with('success','Category Updated Successfully');
        }
        else{
         return redirect()->back()->with('danger','Something happened wrong');
        }
    }
    public function delete($id)
    {
        $category = Category::where('id',$id)->where('seller_id',Auth::user()->id)->delete();
        if($category)
        {
            return redirect()->back()->with('success','Category Deleted Successfully');
        }
        else{
         return redirect()->back()->with('danger','Something happened wrong');
        }
    }

    public function DoDoChatApp()
    {
        return view('settings.dodochat');
    }

    public function dodochat_users_activity()
    {
        $chatLogs = DoDoChat::all();
        return view('settings.dodochat_activity_log', compact('chatLogs'));
    }

    public function dodochat_users_logout($authCode)
    {
        $dodochat = DoDoChat::where('auth_code', trim($authCode))->first();
        $dodochat->logout_time = now();
        $dodochat->save();

        return response()->json([
            'status'=>'success',
        ], 200);
    }
}
