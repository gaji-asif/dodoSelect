<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LazadaProductCategory extends Model
{
    use HasFactory;

    /**
     * Define table name
     *
     * @var string
     */
    protected $table = 'lazada_product_categories';


    /**
     * Relationship to itself
     * Get the 'children' data
     *
     * @return mixed
     */
    public function children()
    {
        return $this->hasMany(self::class, 'category_id', 'id');
    }


    /**
     * Relationship to itself
     * Get the 'parent' data
     *
     * @return mixed
     */
    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id', 'id');
    }
}
