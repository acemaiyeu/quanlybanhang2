<?php

namespace App\Models;

use App\Models\DiscountCondition;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Discount extends Model
{
    use HasFactory;

    protected $table = 'discounts';

    protected $fillable = [
        'id',
        'code',
        'name',
        'start_date',
        'end_date',
        'active',
        'apply_for',
        'data',
        'condition_apply',
        'created_at',
        'created_by',
        'updated_at',
        'updated_by',
        'deleted_at',
        'deleted_by'
    ];

    protected $casts = [
        'data' => 'object'
    ];

    public function conditions()
    {
        return $this->hasMany(DiscountCondition::class, 'discount_id', 'id');
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
