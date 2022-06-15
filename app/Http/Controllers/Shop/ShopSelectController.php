<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Http\Requests\Shop\SelectShopRequest;
use App\Http\Resources\ShopSelectTwoResource;
use App\Models\Shop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ShopSelectController extends Controller
{
    /**
     * Handle the select2 server for shops side from custom_orders
     *
     * @param  \App\Http\Requests\Shop\SelectShopRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function index(SelectShopRequest $request)
    {
        $sellerId = Auth::user()->id;

        $page = $request->get('page');
        $keyword = $request->get('search');
        $extends = $request->get('extends', null);

        $options = [];
        if (! is_null($extends) && isset($extends['options'])) {
            $options = $extends['options'];
        }

        $limit = 10;
        $skip = ($page - 1) * $limit;

        $shops = Shop::selectRaw('id, name')
            ->where('seller_id', $sellerId)
            ->searchFromSelectTwo($keyword)
            ->take($limit)
            ->skip($skip)
            ->orderBy('name')
            ->get();

        $shopsCount = Shop::where('seller_id', $sellerId)
            ->searchFromSelectTwo($keyword)
            ->count();

        $shopArray = $shops->map(function ($shop) {
            return [
                'id' => $shop->id,
                'text' => $shop->name
            ];
        })->toArray();

        $shopsCollection = $shopArray;
        if ($page == 1) {
            $shopsCollection = array_merge($options, $shopArray);
        }

        return response()->json([
            'results' => $shopsCollection,
            'pagination' => [
                'more' => $shopsCount > ($page * $limit)
            ]
        ]);
    }
}
