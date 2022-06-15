<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FacebookAutoreply extends Model
{
    use HasFactory;
    protected $table = 'facebook_autoreply';
    protected $guarded = [];

    public function page()
    {
        $this->belongsTo(FacebookPage::class, 'id', 'page_id');
    }
}
