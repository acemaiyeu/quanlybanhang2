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
        if (!empty($request['product_code'])) {
            $query->whereHas('product', function ($query) use ($request) {
                $query->where('code', 'like', '%' . $request['product_code'] . '%');
            });
        }
        if (!empty($request['category_name'])) {
            $query->whereHas('product', function ($query) use ($request) {
                $query->whereHas('category', function ($query) use ($request) {
                    $query->where('name', 'like', '%' . $request['category_name'] . '%');
                });
            });
        }
        if (!empty($request['category_code'])) {
            $query->whereHas('product', function ($query) use ($request) {
                $query->whereHas('category', function ($query) use ($request) {
                    $query->where('code', 'like', '%' . $request['category_code'] . '%');
                });
            });
        }

        if (!empty($request['sort'])) {
            foreach ($request['sort'] as $key => $value) {
                $query->orderBy($key, $value);
            }
        }
        $query->with('product.unit');
        $limit = $request['limit'] ?? 10;
        return $limit == 1 ? $query->first() : $query->paginate($limit);
    }
}
