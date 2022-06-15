<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class Permission extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'product_id',
        'user_type'
    ];

    public function roles() {
        return $this->belongsToMany(Role::class,'roles_permissions');
    }

    public function users() {
        return $this->belongsToMany(User::class,'users_permissions');
    }

    public function product()
    {
        return $this->belongsTo(Product::class)->withDefault([ 'product_name' => 'Unknown' ]);
    }

    public static function dropshipperProductPermissions($id)
    {
        $dropshipper_id = $id;
        $role_id = DB::table('users_roles')->where('user_id', '=', $dropshipper_id)->pluck('role_id')->first();

        $permission_id_from_role = DB::table('roles_permissions')->where('role_id', '=', $role_id)->get();
        $permission_id_from_user = DB::table('users_permissions')->where('user_id', '=', $dropshipper_id)->get();
        $userPermissions = [];

        foreach ($permission_id_from_user as $permission_id) {
            $permission = Permission::find($permission_id->permission_id);
            $userPermissions[] = $permission->name;
        }
        foreach ($permission_id_from_role as $permission_id) {
            $permission = Permission::find($permission_id->permission_id);
            $userPermissions[] = $permission->name;
        }

        return $userPermissions;
    }

    /**
     * SubQuery to make `product_name` as column
     *
     * @param \Illuminate\Database\Query\Builder $query
     * @return \Illuminate\Database\Query\Builder
     */
    public function scopeProductNameAsColumn($query)
    {
        return $query->addSelect(['product_name' => Product::select('product_name')
            ->whereColumn('id', 'permissions.product_id')
            ->limit(1)
        ]);
    }

    /**
     * SubQuery to make `product price` as column
     *
     * @param \Illuminate\Database\Query\Builder $query
     * @return \Illuminate\Database\Query\Builder
     */
    public function scopeProductPriceAsColumn($query)
    {
        return $query->addSelect(['price' => Product::select('price')
            ->whereColumn('id', 'permissions.product_id')
            ->limit(1)
        ]);
    }

    /**
     * SubQuery to make `dropshipper_price` as column
     *
     * @param \Illuminate\Database\Query\Builder $query
     * @return \Illuminate\Database\Query\Builder
     */
    public function scopeProductDropshipPriceAsColumn($query)
    {
        return $query->addSelect(['dropship_price' => Product::select('dropship_price')
            ->whereColumn('id', 'permissions.product_id')
            ->limit(1)
        ]);
    }

    /**
     * Sub Query to get the quantity
     *
     * @param \Illuminate\Database\Query\Builder $query
     * @return \Illuminate\Database\Query\Builder
     */
    public function scopeQuantity($query)
    {
        return $query->addSelect(['quantity' => ProductMainStock::select('quantity')
            ->whereColumn('product_main_stocks.product_id', 'permissions.product_id')
            ->limit(1)
        ]);
    }

    /**
     * Query to search by from order datatable
     *
     * @param  \Illuminate\Database\Query\Builder  $query
     * @param  string|null  $keyword
     * @return \Illuminate\Database\Query\Builder
     */
    public function scopeSearchDataTable($query, $keyword = null)
    {
        if (!empty($keyword)) {
            return $query->whereHas('product', function(Builder $product) use ($keyword) {
                $product->searchTable($keyword);
                });
        }

        return;
    }
}
