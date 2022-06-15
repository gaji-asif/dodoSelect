<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use DB;

use App\Http\Requests\Category\MassDestroyCategoryRequest;
use App\Http\Requests\Category\StoreCategoryRequest;
use App\Http\Requests\Category\UpdateCategoryRequest;
use Illuminate\Support\Facades\Storage;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $categories = Category::where('parent_category_id',0)->where('seller_id',Auth::user()->id)
        ->orderByRaw('LENGTH(position) asc')
        ->orderBy('position', 'asc')
        ->get();

        $totalSubCat = Category::where('parent_category_id','>',0)
        ->where('seller_id',Auth::user()->id)
        ->count();


        $data = Category::with('children')
        ->where('parent_category_id',0)
        ->where('seller_id',Auth::user()->id)
        ->orderBy('position', 'asc')
        ->get()->toArray();

        $title = 'category';
        return view('settings.category',compact('categories','totalSubCat','data', 'title'));
    }

    /**
     * Get all sub-categories
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAllSubCategories()
    {
        $data['sub_categories'] = Category::where('seller_id',Auth::user()->id)
            ->get();
        return response()->json($data);
    }

    /**
     * Get all sub-categories under a category
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function fetchSubCategory(Request $request)
    {
        $data['sub_categories'] = Category::where('seller_id',Auth::user()->id)
            ->where("parent_category_id", $request->parent_category_id)->get();
        return response()->json($data);
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
    public function updateCategory(Request $request, $id)
    {
        $category = Category::find($id);
        $category->cat_name = $request->cat_name;
        if(!empty($request->parent_category_id))
        {
             $category->parent_category_id = $request->parent_category_id;
        }
        $category->save();

        if($category){
            return redirect('categories')->with('success','Category Updated Successfully');
        }
        else{
         return redirect('categories')->with('danger','Something happened wrong');
        }
    }


    public function reOrder(Request $request)
    {

        //return($request->order);
        foreach ($request->order as $order) {
                DB::table('categories')
                ->where('id',$order['id'])
                ->update([
                    'parent_category_id' =>$order['parent_category_id'],
                    'position' =>$order['position'],
                ]);

            }
            return "success";
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


    public function updateSubCategory(Request $request)
    {
        $category = Category::find($request->id);
        $category->cat_name = $request->cat_name;
        if ($request->hasFile('sub_category_image')) {
            $upload = $request->file('sub_category_image');
            $path =  Storage::disk('s3')->put('uploads/sub_category', $upload,'public');
            $category->image = $path;
        }
        if(!empty($request->parent_category_id))
        {
             $category->parent_category_id = $request->parent_category_id;
        }
        $category->save();

        if($category)
        {
            return redirect('categories')->with('success','Sub Category Updated Successfully');
        }
        else{
         return redirect('categories')->with('danger','Something happened wrong');
        }
    }
}
