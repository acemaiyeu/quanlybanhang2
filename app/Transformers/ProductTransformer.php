<?php

namespace App\Transformers;

use App\Models\Product;
use League\Fractal\TransformerAbstract;

class ProductTransformer extends TransformerAbstract
{
    public function transform(Product $product)
    {
        return [
            'id' => $product->id,
            'code' => $product->code,
            'name' => $product->name,
            'variants' => $product->variants ?? [],
        ];
    }
}
