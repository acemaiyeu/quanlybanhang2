<?php

namespace App\Models;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PermissionDetail extends Model
{
    use HasFactory;

    protected $table = 'permission_details';

    protected $fillable = [
        'id',
        'permission_id',
        'role_id',
        'created_at',
        'created_by',
        'updated_at',
        'updated_by',
        'deleted_at',
        'deleted_by'
    ];

    public function permission()
    {
        return $this->hasOne(Permission::class, 'id', 'permission_id')->select('id', 'code', 'title');
    }

    public function role()
    {
        return $this->hasOne(Role::class, 'id', 'permission_id')->select('id', 'code', 'name');;
    }

    public function createdBy()
    {
        return $this->hasOne(User::class, 'id', 'created_by');
    }

    public function updatedBy()
    {
        return $this->hasOne(User::class, 'id', 'updated_by');
    }

    public function deletedBy()
    {
        return $this->hasOne(User::class, 'id', 'deleted_by');
    }
}
