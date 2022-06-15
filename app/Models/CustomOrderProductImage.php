<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomOrderProductImage extends Model
{
    use HasFactory;

    /**
     * Append custom attributes
     *
     * @var array
     */
    protected $appends = [
        'image_url'
    ];

    /**
     * Relationship to `custom_order_details` table
     *
     * @return mixed
     */
    public function customOrderDetail()
    {
        return $this->belongsTo(CustomOrderDetail::class)->withDefault();
    }

    /**
     * Accessor for `image_url`
     *
     * @return string
     */
    public function getImageUrlAttribute()
    {
        $imageAttribute = $this->attributes['image'] ?? '';

        if (!empty($imageAttribute) && file_exists(public_path('uploads/custom-order/products/' . $imageAttribute))) {
            return asset('uploads/custom-order/products/' . $imageAttribute);
        }

        return asset('No-Image-Found.png');
    }
}
