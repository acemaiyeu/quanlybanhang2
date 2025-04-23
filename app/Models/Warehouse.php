<?php

namespace App\Models;

use App\Models\User;
use App\Models\WarehouseDetail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Warehouse extends Model
{
    use HasFactory;

    protected $table = 'warehouses';

    protected $fillable = [
        'id',
        'code',
        'name',
        'user_id',
        'address',
        'created_at',
        'created_by',
        'updated_at',
        'updated_by',
        'deleted_at',
        'deleted_by'
    ];

    public function distributor()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    public function details()
    {
        return $this->hasMany(WarehouseDetail::class, 'warehouse_id', 'id');
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
