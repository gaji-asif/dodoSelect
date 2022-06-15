<?php

namespace App\Http\Controllers\Category;

use App\Http\Controllers\Controller;
use App\Http\Requests\Category\CategorySelectRequest;
use App\Http\Resources\CategorySelectTwoResource;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CategorySelectController extends Controller
{
    /**
     * Handle select2 ajax
     *
     * @param  \App\Http\Requests\Category\CategorySelectRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function index(CategorySelectRequest $request)
    {
        $sellerId = Auth::user()->id;

        $search = $request->get('search', '');
        $page = $request->get('page', 1);
        $limit = 20;

        $offset = ($page - 1) * $limit;

        $categories = Category::where('seller_id', $sellerId)
                        ->searchSelectTwo($search)
                        ->take($limit)
                        ->skip($offset)
                        ->orderBy('cat_name', 'asc')
                        ->get();

        $categoriesCount = Category::where('seller_id', $sellerId)
                            ->searchSelectTwo($search)
                            ->count();

        if ($page == 1) {
            $allCategoryObject = new Category();
            $allCategoryObject->id = 0;
            $allCategoryObject->cat_name = '- All Categories - ';
            $categories->prepend($allCategoryObject);
        }

        return response()->json([
            'results' => CategorySelectTwoResource::collection($categories),
            'pagination' => [
                'more' => ($page * $limit ) < $categoriesCount
            ]
        ]);
    }
}
