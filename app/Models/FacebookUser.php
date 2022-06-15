<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class FacebookUser extends Model
{
    use HasFactory;
    protected $table = 'facebook_users';
    protected $guarded = [];

    public function pages()
    {
        return $this->hasMany(FacebookPage::class,'user_id', 'user_id');
    }


    public static function getAuthUserPages()
    {
        return FacebookPage::where('user_id', Auth::id())->get();
    }
}
