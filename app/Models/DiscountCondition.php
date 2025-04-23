<?php

namespace App\Models;

use App\Models\Discount;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DiscountCondition extends Model
{
    use HasFactory;

    protected $table = 'discount_conditions';

    protected $fillable = [
        'id',
        'discount_id',
        'condition_apply',
        'condition_data',
        'created_at',
        'created_by',
        'updated_at',
        'updated_by',
        'deleted_at',
        'deleted_by'
    ];

    protected $casts = [
        'condition_data' => 'object'
    ];

    public function discount()
    {
        return $this->hasOne(Discount::class, 'id', 'discount_id');
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
