<?php

namespace App\Transformers;

use App\Models\Warehouse;
use Carbon\Carbon;
use League\Fractal\TransformerAbstract;

class WarehouseTransformer extends TransformerAbstract
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
    public function transform(Warehouse $warehouse)
    {
        return [
            'id' => $warehouse->id,
            'code' => $warehouse->code,
            'name' => $warehouse->name ?? '',
            'distributor_name' => $warehouse->distributor->fullname ?? '',
            'address' => $warehouse->address ?? '',
            'lat' => $warehouse->lat ?? '',
            'long' => $warehouse->lat ?? '',
            'details' => $warehouse->details ?? [],
            'created_at' => Carbon::parse($warehouse->created_at)->format('Y-m-d H:i:s'),
        ];
    }
}
