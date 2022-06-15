<?php

namespace App\Models;

use App\Traits\Models\ThailandAddressQuery;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ThailandProvince extends Model
{
    use HasFactory, ThailandAddressQuery;
}
