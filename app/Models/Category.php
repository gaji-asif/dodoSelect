<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class Category extends Model
{
    use HasFactory;

    protected $table = 'categories';

    /**
     * Mass fillable fields
     *
     * @var array
     */
    protected $fillable = [
        'parent_category_id',
        'specifications',
        'image',
        'cat_name',
        'seller_id',
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
     * Relationship to itself
     * Get the 'children' data
     *
     * @return mixed
     */
    public function children()
    {
        return $this->hasMany(self::class, 'parent_category_id', 'id');
    }

    /**
     * Relationship to itself
     * Get the 'parent' data
     *
     * @return mixed
     */
    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_category_id', 'id');
    }

    /**
     * Relationship to `products` table
     *
     * @return mixed
     */
    public function products()
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Accessor for `image_url`
     *
     * @return string
     */
    public function getImageUrlAttribute()
    {
        $imageAttribute = $this->attributes['image'] ?? '';

        if (Storage::disk('s3')->exists($imageAttribute) && !empty($imageAttribute)) {
            return Storage::disk('s3')->url($imageAttribute);
        }

        return asset('No-Image-Found.png');
    }

    /**
     * Query to search form select2
     *
     * @param  \Illuminate\Database\Query\Builder   $query
     * @param  string|null                          $keyword
     * @return \Illuminate\Database\Query\Builder
     */
    public function scopeSearchSelectTwo($query, $keyword = null)
    {
        if (!empty($keyword)) {
            return $query->where(function($query) use ($keyword) {
                $query->where('cat_name', 'like', "%$keyword%");
            });
        }

        return;
    }


    /**
     * Query to search from datatable
     *
     * @param  \Illuminate\Database\Query\Builder   $query
     * @param  string|null                          $keyword
     * @return \Illuminate\Database\Query\Builder
     */
    public function scopeSearchDatatable($query, $keyword = null)
    {
        if (!empty($keyword)) {
            return $query->where(function($query) use ($keyword) {
                $query->where('cat_name', 'like', "%$keyword%");
            });
        }

        return;
    }

    /**
     * Query to get parent only / top categories
     *
     * @param  \Illuminate\Database\Query\Builder  $query
     * @return  \Illuminate\Database\Query\Builder
     */
    public function scopeParentOnly($query)
    {
        return $query->whereRaw("IFNULL(parent_category_id, 0) = 0");
    }

    /**
     * Query to get children only
     * filter by `parent_category_id` field
     *
     * @param  \Illuminate\Database\Query\Builder  $query
     * @param  integer  $categoryId
     * @return \Illuminate\Database\Query\Builder
     */
    public function scopeChildrenOnly($query, $categoryId = 0)
    {
        if ($categoryId > 0) {
            return $query->whereRaw("IFNULL(parent_category_id, 0) = {$categoryId}");
        }

        return $query->whereRaw("IFNULL(parent_category_id, 0) <> 0");
    }
}
