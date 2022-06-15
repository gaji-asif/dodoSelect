<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ShippingCostResource extends JsonResource
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
            'id' => $this->id,
            'name' => $this->name,
            'price' => $this->price,
            'weight_from' => $this->weight_from,
            'weight_to' => $this->weight_to,
            'shipper' => new ShipperResource($this->whenLoaded('shipper'))
        ];
    }
}
