<?php

namespace App\Transformers;

use App\Models\Promotion;
use Carbon\Carbon;
use League\Fractal\TransformerAbstract;

class PromotionTransformer extends TransformerAbstract
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
    public function transform(Promotion $promotion)
    {
        return [
            'id' => $promotion->id,
            'code' => $promotion->code,
            'name' => $promotion->name,
            'start_date' => $promotion->start_date,
            'end_date' => $promotion->end_date,
            'active' => $promotion->active,
            'condition_apply' => $promotion->condition_apply,
            'apply_for' => $promotion->apply_for,
            'data' => $promotion->data ?? [],
            'created_at' => Carbon::parse($promotion->created_at)->format('Y-m-d H:i:s'),
        ];
    }
}
