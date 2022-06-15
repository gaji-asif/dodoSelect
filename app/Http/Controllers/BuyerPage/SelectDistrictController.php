<?php

namespace App\Http\Controllers\BuyerPage;

use App\Http\Controllers\Controller;
use App\Http\Requests\SelectTwoRequest;
use App\Http\Resources\BuyerPage\DistrictSelectTwoResource;
use App\Models\ThailandDistrict;
use Illuminate\Http\Request;

class SelectDistrictController extends Controller
{
    /**
     * @param  \App\Http\Requests\SelectTwoRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(SelectTwoRequest $request)
    {
        $page = $request->get('page');
        $search = $request->get('search');
        $provinceCode = $request->get('province_code');

        $take = 20;
        $skip = ($page - 1) * $take;

        $districts = ThailandDistrict::query()
                        ->byProvince($provinceCode)
                        ->searchSelectTwo($search)
                        ->take($take)
                        ->skip($skip)
                        ->orderBy('name_th')
                        ->get();

        $districtCount = ThailandDistrict::query()
                        ->byProvince($provinceCode)
                        ->searchSelectTwo($search)
                        ->count();

        $selectSource = [
            'results' => DistrictSelectTwoResource::collection($districts),
            'pagination' => [
                'more' => $districtCount > ($page * $take),
            ]
        ];

        return response()->json($selectSource);
    }
}
