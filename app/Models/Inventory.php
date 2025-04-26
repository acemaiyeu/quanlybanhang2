<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{
    use HasFactory;

    protected $table = 'inventories';

    protected $fillable = [
        'id',
        'product_id',
        'variant_id',
        'warehouse_id',
        'quantity',
        'location',
        'status',
        'batch_number',
        'expiration_date',
        'unit_id',
        'note',
        'created_at',
        'created_by',
        'updated_at',
        'updated_by',
        'deleted_at',
        'deleted_by'
    ];

    public function product()
    {
        return $this->hasOne(Product::class, 'id', 'product_id')->whereNull('deleted_at')->select('id', 'code', 'name');
    }

    public function variant()
    {
        return $this->hasOne(Variant::class, 'id', 'variant_id')->whereNull('deleted_at')->select('id', 'product_id', 'variants_info', 'thumbnail');
    }

    public function warehouse()
    {
        return $this->hasOne(Warehouse::class, 'id', 'warehouse_id')->whereNull('deleted_at')->select('id', 'code', 'name');
    }

    public function unit()
    {
        return $this->hasOne(Unit::class, 'id', 'unit_id')->whereNull('deleted_at')->select('id', 'name');
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
