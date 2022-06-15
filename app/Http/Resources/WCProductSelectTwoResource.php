<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class WCProductSelectTwoResource extends JsonResource
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
            'id' => $this->product_id,
            'text' => $this->product_name.' ( '.$this->product_code. ' )'
        ];
    }
}
