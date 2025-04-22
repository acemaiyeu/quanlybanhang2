<?php

namespace App\Models;

use App\Models\Product;
use App\Models\User;
use App\Models\Variant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Variant extends Model
{
    use HasFactory;

    protected $table = 'variants';

    protected $fillable = [
        'id',
        'code',
        'name',
        'product_id',
        'variants_info',
        'thumbnail',
        'images',
        'price',
        'created_at',
        'created_by',
        'updated_at',
        'updated_by',
        'deleted_at',
        'deleted_by'
    ];

    protected $casts = [
        'variants_info' => 'array',
        'images' => 'array',
    ];

    public function product()
    {
        return $this->hasOne(Product::class, 'id', 'product_id')->select('id', 'code', 'name', 'unit_id');  // ->with('unit')->except('updated_at', 'updated_by', 'deleted_at', 'deleted_by');
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
