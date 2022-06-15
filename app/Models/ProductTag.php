<?php

namespace App\Models;

use App\Traits\HasProductTagsTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductTag extends Model
{
    use HasFactory, SoftDeletes, HasProductTagsTrait;

    public function products() {
        return $this->belongsToMany(Product::class,'product_has_tags', 'product_tag_id', 'product_id');
    }
}
