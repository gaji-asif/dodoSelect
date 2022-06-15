<?php

namespace App\Permissions;

use App\Models\Permission;
use App\Models\Role;
use Exception;
use Illuminate\Support\Collection;

trait HasPermissionsTrait {

    public function givePermissionsTo(... $permissions) {

        $permissions = $this->getAllPermissions($permissions);
//        dd($permissions);
        if($permissions === null) {
            return $this;
        }
        $this->permissions()->saveMany($permissions);
        return $this;
    }

    public function withdrawRolesTo( ... $roles ) {

        $role = $this->getAllRoles($roles);
        $this->roles()->detach($role);
        return $this;
    }

    public function refreshPermissions( ... $permissions ) {

        $this->permissions()->detach();
        return $this->givePermissionsTo($permissions);
    }

    public function hasPermissionTo($permission) {

        return $this->hasPermissionThroughRole($permission) || $this->hasPermission($permission);
    }

    public function hasPermissionThroughRole($permission) {

        foreach ($permission->roles as $role){
            if($this->roles->contains($role)) {
                return true;
            }
        }
        return false;
    }

    public function hasRole( ... $roles ) {

        foreach ($roles as $role) {
            if ($this->roles->contains('name', $role)) {
                return true;
            }
        }
        return false;
    }

    public function getRoleNames(): Collection
    {
        return $this->roles->pluck('name');
    }

    public function getRoleId(): Collection
    {
        return $this->roles->pluck('id');
    }

    public function getPermissionNames()
    {
        return $this->permissions->pluck('name');
    }

    public function roles() {

        return $this->belongsToMany(Role::class,'users_roles');
    }

    public function permissions() {

        return $this->belongsToMany(Permission::class,'users_permissions');
    }

    protected function hasPermission($permission) {

        return (bool) $this->permissions->where('name', $permission->name)->count();
    }

    protected function getAllRoles(array $rolse) {

        return Role::whereIn('name',$rolse)->get();
    }

    public function getPermissionsViaRoles()
    {
        $relationships = ['roles', 'roles.permissions'];

        if (method_exists($this, 'loadMissing')) {
            $this->loadMissing($relationships);
        } else {
            $this->load($relationships);
        }

        return $this->roles->flatMap(function ($role) {
            return $role->permissions;
        })->sort()->values();
    }

    /**
     * Return all the permissions the model has, both directly and via roles.
     *
     * @throws Exception
     */
    public function getAllPermissions()
    {
        $permissions = $this->permissions;

        if ($this->roles) {
            $permissions = $permissions->merge($this->getPermissionsViaRoles());
        }

        return $permissions->sort()->values();
    }

}
