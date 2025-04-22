<?php
namespace App\ModelQuery;

use App\Models\Variant;
use Illuminate\Support\Facades\DB;

class VariantModel
{
    public function getVariantProduct($request)
    {
        $query = Variant::query();
        $query->whereNull('deleted_at');
        if (!empty($request['product_name'])) {
            $query->whereHas('product', function ($query) use ($request) {
                $query->where('name', 'like', '%' . $request['product_name'] . '%');
            });
        }
        $query->with('product.unit');
        $limit = $request['limit'] ?? 10;
        return $limit == 1 ? $query->first() : $query->paginate($limit);
    }
}
