<?php

namespace App\Transformers;

use App\Models\Inventory;
use Carbon\Carbon;
use League\Fractal\TransformerAbstract;

class InventoryTransformer extends TransformerAbstract
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
    public function transform(Inventory $inventory)
    {
        return [
            'id' => $inventory->id,
            'product' => $inventory->product ?? null,
            'variant' => $inventory->variant ?? '',
            'warehouse' => $inventory->warehouse ?? '',
            'quantity' => $inventory->quantity ?? 0,
            'location' => $inventory->location ?? '',
            'status' => $inventory->status == 'IMPORT' ? 'Nhập kho' : 'Xuất kho',
            'batch_number' => $inventory->batch_number ?? '',
            'expiration_date' => $inventory->expiration_date ?? '',
            'unit' => $inventory->unit ?? null,
            'notes' => $inventory->notes,
            'created_at' => Carbon::parse($inventory->created_at)->format('Y-m-d H:i:s'),
        ];
    }
}
