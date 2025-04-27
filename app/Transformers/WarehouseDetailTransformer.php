<?php

namespace App\Transformers;

use App\Models\WarehouseDetail;
use Carbon\Carbon;
use League\Fractal\TransformerAbstract;

class WarehouseDetailTransformer extends TransformerAbstract
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
    public function transform(WarehouseDetail $warehouse_detail)
    {
        return [
            'id' => $warehouse_detail->id,
            'warehouse' => $warehouse_detail->warehouse,
            'variant' => $warehouse_detail->variant,
            'quantity' => $warehouse_detail->quantity,
            'created_at' => Carbon::parse($warehouse_detail->created_at)->format('Y-m-d H:i:s'),
        ];
    }
}
