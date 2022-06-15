<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DomesticShipper extends Model
{
    use HasFactory;

    protected $table = 'domestic_shippers';
    /**
     * Mass fillable fields
     *
     * @var array
     */
    protected $fillable = [
        'name',
    ];

  

}
