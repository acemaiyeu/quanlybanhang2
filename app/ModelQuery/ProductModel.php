<?php
namespace App\ModelQuery;

use App\Models\Category;
use App\Models\Product;
use App\Models\Unit;
use Illuminate\Support\Facades\DB;

class ProductModel
{
    public function getProducts($request)
    {
        $query = Product::query();
        $query->whereNull('deleted_at');

        if (!empty($request['category_id'])) {
            $query->where('category_id', $request['category_id']);
        }
        if (!empty($request['category_code'])) {
            $query->whereHas('category', function ($query) use ($request) {
                $query->where('code', $request['category_code']);
            });
        }
        if (!empty($request['category_name'])) {
            $query->whereHas('category', function ($query) use ($request) {
                $query->where('name', 'like', '%' . $request['category_name'] . '%');
            });
        }
        if (!empty($request['code'])) {
            $query->where('code', $request['code']);
        }
        if (!empty($request['name'])) {
            $query->where('name', 'like', '%' . $request['name'] . '%');
        }

        $query->with('category', 'variants');
        $limit = $request['limit'] ?? 10;
        return $limit === 1 ? $query->first() : $query->paginate($limit);
    }

    public function createProduct($request)
    {
        try {
            DB::beginTransaction();
            $product = new Product();
            $product->code = $request['code'];
            $product->name = $request['name'];
            $product->category_id = $request['category_id'];
            $product->unit_id = $request['unit_id'];
            $product->created_at = date('Y-m-d H:i:s');
            $product->created_by = auth()->user()->id;
            $product->save();

            DB::commit();
        } catch (Exception $e) {
            return response()->json($e->getMessage(), 400);
        }
        return $product;
    }

    public function updateProduct($request)
    {
        try {
            DB::beginTransaction();
            $product = Product::whereNull('deleted_at')->where('code', $request['code'])->first();
            if (!empty($request['category_id'])) {
                $category = Category::whereNull('deleted_at')->find($request['category_id']);
                if (!$category) {
                    return response()->json([
                        'message' => 'Danh mục không tồn tại',
                        'status' => false,
                    ], 400);
                }
            }
            if (!empty($request['unit_id'])) {
                $unit = Unit::whereNull('deleted_at')->find($request['unit_id']);
                if (!$unit) {
                    return response()->json([
                        'message' => 'Đơn vị không tồn tại',
                        'status' => false,
                    ], 400);
                }
            }

            $product->name = $request['name'] ?? $product->name;
            $product->category_id = $request['category_id'] ?? $product->category_id;
            $product->unit_id = $request['unit_id'] ?? $product->unit_id;
            $product->created_at = date('Y-m-d H:i:s');
            $product->created_by = auth()->user()->id;
            $product->save();

            DB::commit();
            return $product;
        } catch (Exception $e) {
            return response()->json($e->getMessage(), 400);
        }
    }

    public function deleteProduct($request)
    {
        try {
            DB::beginTransaction();
            $product = Product::whereNull('deleted_at')->where('code', $request['code'])->first();

            $product->deleted_at = date('Y-m-d H:i:s');
            $product->deleted_by = auth()->user()->id;
            $product->save();

            DB::commit();
            return $product;
        } catch (Exception $e) {
            return response()->json($e->getMessage(), 400);
        }
    }
}
