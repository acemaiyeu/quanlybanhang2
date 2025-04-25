<?php

namespace App\Models;

use App\Models\Category;
use App\Models\Unit;
use App\Models\User;
use App\Models\Variant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $table = 'products';

    protected $fillable = [
        'id',
        'code',
        'name',
        'short_description',
        'descriptions',
        'category_id',
        'unit_id',
        'created_at',
        'created_by',
        'updated_at',
        'updated_by',
        'deleted_at',
        'deleted_by'
    ];

    public function category()
    {
        return $this->hasOne(Category::class, 'id', 'category_id')->select('id', 'code', 'name');;
    }

    public function variants()
    {
        return $this->hasMany(Variant::class, 'product_id', 'id')->select(
            'id',
            'product_id',
            'price',
            'variants_info',
            'thumbnail',
            'images'
        );
    }

    public function unit()
    {
        return $this->hasOne(Unit::class, 'id', 'unit_id')->select('id', 'name');
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
