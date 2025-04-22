<?php

namespace App\Transformers;

use App\Models\Variant;
use League\Fractal\TransformerAbstract;

class VariantTransformer extends TransformerAbstract
{
    /**
     * List of resources to automatically include
     *
     * @var array
     */
    protected array $defaultIncludes = [
        //
    ];

    /**
     * List of resources possible to include
     *
     * @var array
     */
    protected array $availableIncludes = [
        //
    ];

    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public function transform(Variant $variant)
    {
        return [
            'id' => $variant->id,
            'product_id' => $variant->product_id,
            'variant_info' => $variant->variants_info ?? [],
            'thumbnail' => $Variant->thumbnail ?? '',
            'images' => $variant->images ?? [],
            'product' => $variant->product ?? null,
            'price' => $variant->price,
            'price_text' => number_format($variant->price, 0, ',', '.') . ' đ'
        ];
    }
}
