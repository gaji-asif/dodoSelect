<?php

namespace App\Http\Controllers\BuyerPage;

use App\Http\Controllers\Controller;
use App\Http\Requests\SelectTwoRequest;
use App\Http\Resources\BuyerPage\ProvinceSelectTwoResource;
use App\Models\ThailandProvince;
use Illuminate\Http\Request;

class SelectProvinceController extends Controller
{
    /**
     * @param  \App\Http\Requests\SelectTwoRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(SelectTwoRequest $request)
    {
        $page = $request->get('page');
        $search = $request->get('search');

        $take = 20;
        $skip = ($page - 1) * $take;

        $provinces = ThailandProvince::query()
                        ->searchSelectTwo($search)
                        ->take($take)
                        ->skip($skip)
                        ->orderBy('name_th')
                        ->get();

        $provinceCount = ThailandProvince::query()
                        ->searchSelectTwo($search)
                        ->count();

        $selectSource = [
            'results' => ProvinceSelectTwoResource::collection($provinces),
            'pagination' => [
                'more' => $provinceCount > ($page * $take),
            ]
        ];

        return response()->json($selectSource);
    }
}
