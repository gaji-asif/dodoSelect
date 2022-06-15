<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FacebookAutoreplyCampaign extends Model
{
    use HasFactory;
    protected $table = 'facebook_autoreply_campaign';
    protected $guarded = [];
}
