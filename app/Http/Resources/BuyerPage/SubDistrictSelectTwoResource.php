<?php

namespace App\Http\Resources\BuyerPage;

use Illuminate\Http\Resources\Json\JsonResource;

class SubDistrictSelectTwoResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->name_th,
            'text' => $this->name_th . ' (' . $this->name_en . ')',
            'code' => $this->code,
            'name_en' => $this->name_en,
            'district_code' => $this->district_code
        ];
    }
}
