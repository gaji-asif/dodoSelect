<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class FacebookPage extends Model
{
    use HasFactory;
    protected $table = 'facebook_pages';
    protected $guarded = [];

    public function users()
    {
        return $this->belongsTo(FacebookUser::class, 'user_id', 'user_id');
    }

    public function getPageProfileAttribute($value)
    {
        return Storage::disk('s3')->url(json_decode($value)->url);
    }
}
