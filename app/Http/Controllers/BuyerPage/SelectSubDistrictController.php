<?php

namespace App\Http\Controllers\BuyerPage;

use App\Http\Controllers\Controller;
use App\Http\Requests\SelectTwoRequest;
use App\Http\Resources\BuyerPage\SubDistrictSelectTwoResource;
use App\Models\ThailandSubDistrict;
use Illuminate\Http\Request;

class SelectSubDistrictController extends Controller
{
    /**
     * @param  \App\Http\Requests\SelectTwoRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(SelectTwoRequest $request)
    {
        $page = $request->get('page');
        $search = $request->get('search');
        $districtCode = $request->get('district_code');

        $take = 20;
        $skip = ($page - 1) * $take;

        $districts = ThailandSubDistrict::query()
                        ->byDistrict($districtCode)
                        ->searchSelectTwo($search)
                        ->take($take)
                        ->skip($skip)
                        ->orderBy('name_th')
                        ->get();

        $districtCount = ThailandSubDistrict::query()
                        ->byDistrict($districtCode)
                        ->searchSelectTwo($search)
                        ->count();

        $selectSource = [
            'results' => SubDistrictSelectTwoResource::collection($districts),
            'pagination' => [
                'more' => $districtCount > ($page * $take),
            ]
        ];

        return response()->json($selectSource);
    }
}
