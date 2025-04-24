<?php

namespace App\Models;

use App\Models\Discount;
use App\Models\Order;
use App\Models\User;
use App\Models\Variant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderDetail extends Model
{
    use HasFactory;

    protected $table = 'order_details';

    protected $fillable = [
        'id',
        'cart_id',
        'variant_id',
        'price',
        'quantity',
        'discount_id',
        'discount_code',
        'discount_price',
        'total_discount',
        'total_price',
        'created_at',
        'created_by',
        'updated_at',
        'updated_by',
        'deleted_at',
        'deleted_by'
    ];

    public function order()
    {
        return $this->hasOne(Order::class, 'id', 'cart_id');
    }

    public function variant()
    {
        return $this->hasOne(Variant::class, 'id', 'variant_id');
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
