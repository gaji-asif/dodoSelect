<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Shop extends Model
{
    use HasFactory;

    /**
     * Append custom attributes
     *
     * @var array
     */
    protected $appends = [
        'logo_url'
    ];

    /**
     * Get woo_shop data
     *
     * @return HasMany
     */
    public function wooShops()
    {
        return $this->hasMany(WooShop::class, 'shop_id', 'id');
    }

    /**
     * Accessor for `image_url`
     *
     * @return string
     */
    public function getLogoUrlAttribute()
    {
        $logoAttribute = $this->attributes['logo'] ?? '';

        if (!empty($logoAttribute) && Storage::disk('s3')->exists($logoAttribute)) {
            return Storage::disk('s3')->url($logoAttribute);
        }

        return asset('No-Image-Found.png');
    }

    /**
     * Query to search from `select2`
     *
     * @param  \Illuminate\Database\Query\Builder  $query
     * @param  string|null $keyword
     * @return \Illuminate\Database\Query\Builder
     */
    public function scopeSearchFromSelectTwo($query, $keyword = null)
    {
        if (!empty($keyword)) {
            return $query->where(function(Builder $shop) use ($keyword) {
                $shop->where('name', 'like', '%'. $keyword .'%');
            });
        }

        return;
    }
}
