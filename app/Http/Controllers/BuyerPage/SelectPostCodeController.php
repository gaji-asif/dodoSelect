<?php

namespace App\Http\Controllers\BuyerPage;

use App\Http\Controllers\Controller;
use App\Http\Requests\SelectTwoRequest;
use App\Http\Resources\BuyerPage\PostCodeSelectTwoResource;
use App\Models\ThailandPostCode;
use Illuminate\Http\Request;

class SelectPostCodeController extends Controller
{
    /**
     * @param  \App\Http\Requests\SelectTwoRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(SelectTwoRequest $request)
    {
        $page = $request->get('page');
        $search = $request->get('search');
        $subDistrictCode = $request->get('sub_district_code');

        $take = 20;
        $skip = ($page - 1) * $take;

        $postCodes = ThailandPostCode::query()
                        ->selectRaw('sub_district_code, post_code')
                        ->bySubDistrict($subDistrictCode)
                        ->searchSelectTwo($search)
                        ->take($take)
                        ->skip($skip)
                        ->groupBy('post_code')
                        ->orderBy('post_code')
                        ->get();

        $postCodeCount = ThailandPostCode::query()
                        ->bySubDistrict($subDistrictCode)
                        ->searchSelectTwo($search)
                        ->groupBy('post_code')
                        ->count();

        $selectSource = [
            'results' => PostCodeSelectTwoResource::collection($postCodes),
            'pagination' => [
                'more' => $postCodeCount > ($page * $take),
            ]
        ];

        return response()->json($selectSource);
    }
}
