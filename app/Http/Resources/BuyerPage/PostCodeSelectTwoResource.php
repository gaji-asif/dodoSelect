<?php

namespace App\Http\Resources\BuyerPage;

use Illuminate\Http\Resources\Json\JsonResource;

class PostCodeSelectTwoResource extends JsonResource
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
            'id' => $this->post_code,
            'text' => $this->post_code,
            'sub_district_code' => $this->sub_district_code
        ];
    }
}
