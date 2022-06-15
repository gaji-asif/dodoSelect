<?php

namespace App\Models;

use App\Permissions\HasPermissionsTrait;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Auth;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasPermissionsTrait;

    /**
     * Define `is_active` field
     *
     * @var mixed
     */
    CONST ACTIVE = 1;
    CONST NON_ACTIVE = 0;

    /**
     * Define `role` field
     *
     * @var mixed
     */
    CONST ROLE_MEMBER = 'member';
    CONST ROLE_ADMIN = 'admin';
    CONST ROLE_STAFF = 'staff';
    CONST ROLE_DROPSHIPPER = 'dropshipper';

    /**
     * Available pref_lang
     *
     * @var mixed
     */
    CONST PREF_LANG_ENGLISH = 'en';
    CONST PREF_LANG_THAI = 'th';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $guarded = [
        'id'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * Append custom attributes
     *
     * @var array
     */
    protected $appends = [
        'avatar_url'
    ];

    public function orders()
    {
        return $this->hasMany(Order::class, 'shop_id', 'shop_id');
    }

    public function permissions() {
        return $this->belongsToMany(Permission::class,'users_permissions');
    }

    public function roles() {
        return $this->belongsToMany(Role::class,'users_roles');
    }

    public function shop() {
        return $this->belongsTo(Shop::class)->withDefault(['name' => '']);
    }

    public function sheetDocs()
    {
        return $this->hasMany(SheetDoc::class, 'seller_id', 'id');
    }

    public function getIdAttribute()
    {
        if (isset(Auth::user()->id) && Auth::user()->role == 'staff'){
            return $this->attributes['seller_id'];
        }

        if (isset(Auth::user()->id) && Auth::user()->role == 'dropshipper'){
            return $this->attributes['seller_id'];
        }

        return $this->attributes['id'];
    }

    public function getStaffIdAttribute()
    {
        if (isset(Auth::user()->id) && Auth::user()->role == 'staff'){
            return $this->attributes['id'];
        }

        return $this->attributes['id'];
    }

    public function getDropshipperIdAttribute()
    {
        if (isset(Auth::user()->id) && Auth::user()->role == 'dropshipper'){
            return $this->attributes['id'];
        }

        return $this->attributes['id'];
    }

    /**
     * Accessor for `avatar_url`
     *
     * @return string
     */
    public function getAvatarUrlAttribute()
    {
        $logoAttribute = $this->attributes['logo'] ?? '';

        if (!empty($logoAttribute) && file_exists(public_path($logoAttribute))) {
            return asset($logoAttribute);
        }

        return asset('img/male-avatar.svg');
    }

    /**
     * Query to search by `name`
     *
     * @param Builder
     * @param string|null $keyword
     * @return Builder
     */
    public function scopeSearchByName($query, $keyword = null)
    {
        if (!empty($keyword)) {
            return $query->where('name', 'like', "%$keyword%");
        }

        return;
    }

    public static function userSeller(){
        $data = User::where([
                    'seller_id' => Auth::user()->id
                ])->first();

        if(!empty(Auth::user()->seller_id)){
            return $seller_id = Auth::user()->seller_id;
        }
        else{
            return $seller_id = Auth::user()->id;
        }
    }

    public function dropshipperAddress(){
        return $this->hasOne(DropshipperAddress::class, 'user_id', 'id')->withDefault(['address' => '']);
    }

    /**
     * Sub Query to get the total orders amount for dropshippers
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeTotalOrdersAmount($query)
    {
        $userTable = $this->getTable();
        $ordersTable = (new OrderManagement())->getTable();

        return $query->addSelect(['total_amount' => OrderManagement::selectRaw("SUM({$ordersTable}.in_total)")
            ->whereColumn("{$ordersTable}.customer_id", "{$userTable}.customer_id")
            ->limit(1)
        ]);
    }

    /**
     * Sub Query to get the total orders count for dropshippers
     *
     * @param  Builder  $query
     * @return Builder
     */
    public function scopeTotalOrders($query)
    {
        $userTable = $this->getTable();
        $ordersTable = (new OrderManagement())->getTable();

        return $query->addSelect(['total_orders' => OrderManagement::selectRaw("COUNT(id)")
            ->whereColumn("{$ordersTable}.customer_id", "{$userTable}.customer_id")
            ->limit(1)
        ]);
    }


    /**
     * Get all pref_lang values
     *
     * @return array
     */
    public static function getAllPrefLang()
    {
        return [
            self::PREF_LANG_ENGLISH => 'English',
            self::PREF_LANG_THAI => 'Thai'
        ];
    }
}
