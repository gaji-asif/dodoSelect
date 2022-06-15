<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Channel extends Model
{
    use HasFactory;

    protected $table = 'channels';
    /**
     * Mass fillable fields
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'image',
    ];

    /**
     * Appends custom attributes
     *
     * @var array
     */
    protected $appends = [
        'image_url'
    ];

    /**
     * Relationship to `order_managements` table
     *
     * @return mixed
     */
    public function orderProduct()
    {
        return $this->hasMany(OrderManagement::class, 'channel_id', 'id');
    }

    /**
     * Accessor for `image_url`
     *
     * @return string
     */
    public function getImageUrlAttribute()
    {
        $imageAttribute = $this->attributes['image'] ?? '';

        if (!empty($imageAttribute) && file_exists(public_path($imageAttribute))) {
            return asset($imageAttribute);
        }

        return asset('No-Image-Found.png');
    }
}
