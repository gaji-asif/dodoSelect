<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WooShop extends Model
{
    use HasFactory;

    /**
     * Get shop data
     *
     * @return BelongsTo
     */
    public function shops()
    {
        return $this->belongsTo(Shop::class, 'shop_id', 'id')->withDefault(['name' => '']);
    }

    /**
     * Get WooCommerce Product data
     *
     * @return HasMany
     */
    public function wooProducts()
    {
        return $this->hasMany(WooProduct::class, 'website_id', 'id');
    }

    public static function is_valid($shop_url)
    {
        $shop = '';
        if(isset(parse_url($shop_url)['host'])):
            $shop = self::where('site_url', 'like' ,'%'.parse_url($shop_url)['host'].'%')->first();
        endif;
        return $shop !== null ? $shop : false;
    }

    public static function get_supplier_id($seller_id)
    {
        $seller = Supplier::where('seller_id', $seller_id)->first();
        return $seller !== null ? $seller->seller_id : null;
    }
}
