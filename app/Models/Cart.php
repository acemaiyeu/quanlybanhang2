<?php

namespace App\Models;

use App\Models\CartDetail;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    use HasFactory;

    protected $table = 'carts';

    protected $fillable = [
        'id',
        'user_id',
        'session_id',
        'fullname',
        'user_phone',
        'user_address',
        'discount_code',
        'total_discount',
        'fee_ship_code',
        'fee_ship',
        'method_payment',
        'gifts',
        'note',
        'total_price',
        'info_payment',
        'created_at',
        'created_by',
        'updated_at',
        'updated_by',
        'deleted_at',
        'deleted_by'
    ];

    protected $casts = [
        'gifts' => 'array'
    ];

    public function details()
    {
        return $this->hasMany(CartDetail::class, 'cart_id', 'id')->whereNull('deleted_at')->select('id', 'cart_id', 'variant_id', 'discount_id', 'discount_code', 'discount_price', 'total_discount', 'price', 'quantity', 'total_price');
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
