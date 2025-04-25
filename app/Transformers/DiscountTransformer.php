<?php

namespace App\Transformers;

use App\Models\Discount;
use Carbon\Carbon;
use League\Fractal\TransformerAbstract;

class DiscountTransformer extends TransformerAbstract
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
    public function transform(Discount $discount)
    {
        return [
            'id' => $discount->id,
            'code' => $discount->code,
            'name' => $discount->name,
            'start_date' => $discount->start_date,
            'end_date' => $discount->end_date,
            'active' => $discount->active,
            'condition_apply' => $discount->condition_apply,
            'apply_for' => $discount->apply_for,
            'data' => $discount->data ?? [],
            'created_at' => Carbon::parse($discount->created_at)->format('Y-m-d H:i:s'),
        ];
    }
}
