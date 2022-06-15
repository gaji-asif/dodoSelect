<?php

namespace App\Models;

use App\Permissions\HasPermissionsTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class Role extends Model
{
    use HasFactory, HasPermissionsTrait;

    protected $fillable = [
        'name',
        'user_type',
        'description',
    ];

    public function permissions() {
        return $this->belongsToMany(Permission::class,'roles_permissions');
    }

    public function users() {
        return $this->belongsToMany(User::class,'users_roles');
    }

    public static function checkRolePermissions(string $check_permission){

        if (Auth::user()->role == 'staff') {
            $staff_id = Auth::user()->staff_id;
            $role_id = DB::table('users_roles')->where('user_id', $staff_id)->pluck('role_id')->first();
            $role = Role::find($role_id);
            $permissions = $role->getPermissionNames()->toArray();

            $result = in_array($check_permission, $permissions);
            return $result;

        } elseif (Auth::user()->role == 'dropshipper') {
            $permissions = ['Can access menu: Order Management', 'Can access menu: Product'];
            $result = in_array($check_permission, $permissions);

            return $result;

        } else{
            return true;
        }
    }
}
