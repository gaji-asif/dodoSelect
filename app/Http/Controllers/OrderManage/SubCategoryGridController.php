<?php

namespace App\Http\Controllers\OrderManage;

use App\Http\Controllers\Controller;
use App\Http\Requests\OrderManagement\SubCategory\GridRequest;
use App\Http\Resources\SubCategoryGridResource;
use App\Models\Category;
use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SubCategoryGridController extends Controller
{
    /**
     * Handle datatable server side
     *
     * @param  \App\Http\Requests\OrderManagement\SubCategory\GridRequest  $request
     * @return mixed
     */
    public function index(GridRequest $request)
    {
        $sellerId = Auth::user()->id;
        $categoryId = $request->get('categoryId');

        $page = $request->get('page');
        $search = $request->get('search');

        $limit = 20;
        $skip = ($page - 1) * $limit;

        if (Auth::user()->role == 'dropshipper') {
            $userPermissions = Permission::dropshipperProductPermissions(Auth::user()->dropshipper_id);
            $subCategories = Category::where('seller_id', $sellerId)
                ->childrenOnly($categoryId)
                ->searchDatatable($search)
                ->whereHas('products', function ($query) use ($userPermissions) {
                    return $query->whereIn('products.product_code', $userPermissions);
                })
                ->orderBy('cat_name', 'asc')
                ->take($limit)
                ->skip($skip)
                ->get();

        } else {
            $subCategories = Category::where('seller_id', $sellerId)
                ->childrenOnly($categoryId)
                ->searchDatatable($search)
                ->whereHas('products')
                ->orderBy('cat_name', 'asc')
                ->take($limit)
                ->skip($skip)
                ->get();
        }

        $subCategoriesCount = Category::where('seller_id', $sellerId)
                                ->childrenOnly($categoryId)
                                ->searchDatatable($search)
                                ->count();

        return response()->json([
            'results' => SubCategoryGridResource::collection($subCategories),
            'pagination' => [
                'more' => ($page * $limit) < $subCategoriesCount
            ]
        ]);
    }
}
