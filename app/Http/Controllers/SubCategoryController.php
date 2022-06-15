<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Datatables;
use DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;

class SubCategoryController extends Controller
{
    public function index()
    {
        $categories = Category::where('parent_category_id', '=', 0)->where('seller_id',Auth::user()->id)->get();
        $data = Category::with('children')
        ->where('seller_id',Auth::user()->id)
        ->where('parent_category_id', '!=', 0)
        ->get();
        $title = 'sub-category';
        return view('settings.subCategory',compact('categories','data', 'title'));
    }

    public function data(Request $request)
    {
        $categories = Category::where('parent_category_id',0)->where('seller_id',Auth::user()->id)->get();

        if ($request->ajax()){
            if (isset($request->id) && $request->id != null) {
                $editData = Category::where([
                    'id' => $request->id
                ])->first();
                $data = [
                    'categories' => $categories,
                    'editData' => $editData,
                ];
                return view('elements.form-update-sub-category',compact( 'editData', 'categories'));
            }

            $data = Category::with('children')
                ->where('seller_id',Auth::user()->id)
                ->where('parent_category_id', '!=', 0)
                ->get();

            $table = Datatables::of($data)
                ->addColumn('image', function ($row) {
                    if(!empty($row->image) && file_exists(public_path($row->image))) {
                        return '<span><img src="'.asset($row->image).'" class="cutome_image" ></span>';
                    }
                    return '<span><img src="'.asset('No-Image-Found.png').'" class="cutome_image" ></span>';
                })
                ->addColumn('children', function ($row) {
                    return $row->children->cat_name;
                })
                ->addColumn('manage', function ($row) {
                    return '
                        <div class="w-full text-center">
                            <button type="button" class="modal-open btn-action--green" title="'. __('translation.Edit') .'" x-on:click="showEditModal=true" data-id="' . $row->id . '" id="BtnUpdate">
                                <i class="fas fa-pencil-alt"></i>
                            </button>
                            <button type="button" class="btn-action--red" title="'. __('translation.Delete') .'" data-id="' . $row->id . '" id="BtnDelete">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </div>
                    ';
                })
                ->rawColumns(['image', 'children','manage'])
                ->make(true);

            return $table;
        }
    }


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $category = new Category();
        $category->cat_name = $request->cat_name;
        if ($request->hasFile('sub_category_image')) {
            $upload = $request->file('sub_category_image');
            $file_type = $upload->getClientOriginalExtension();
            $upload_name =  time() . $upload->getClientOriginalName();
            $destinationPath = public_path('uploads/sub_category');
            $upload->move($destinationPath, $upload_name);
            $category->image = 'uploads/sub_category/'.$upload_name;
        }
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
            return redirect()->back()->with('success','Sub Category Added Successfully');
        }
        else{
         return redirect()->back()->with('danger','Something happened wrong');
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
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
    }
    public function updateCategory(Request $request)
    {
        $category = Category::find($request->id);
        $category->cat_name = $request->cat_name;
        if ($request->hasFile('sub_category_image')) {
            $upload = $request->file('sub_category_image');
            $file_type = $upload->getClientOriginalExtension();
            $upload_name =  time() . $upload->getClientOriginalName();
            $destinationPath = public_path('uploads/sub_category');
            $upload->move($destinationPath, $upload_name);
            $category->image = 'uploads/sub_category/'.$upload_name;
        }
        if(!empty($request->parent_category_id))
        {
             $category->parent_category_id = $request->parent_category_id;
        }
        $category->save();

        if($category)
        {
            return redirect('sub_categories')->with('success','Sub Category Updated Successfully');
        }
        else{
         return redirect('sub_categories')->with('danger','Something happened wrong');
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
        } else {

            $category = Category::find($request->id);

            if(file_exists($category->image)){
                unlink($category->image);
            }

            DB::table('categories')->where([
                'id' => $request->id
            ])->delete();

            return [
                'status' => 1
            ];
        }
//        $category = Category::where('id',$id)->where('seller_id',Auth::user()->id)->delete();
//        if($category)
//        {
//            return redirect()->back()->with('success','Sub Category Deleted Successfully');
//        }
//        else{
//         return redirect()->back()->with('danger','Something happened wrong');
//        }
    }
}
