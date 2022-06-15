<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductMainStock extends Model
{
    use HasFactory;

    /**
     * Relationship to `products` table
     *
     * @return mixed
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
