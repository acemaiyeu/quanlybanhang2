<?php
namespace App\ModelQuery;

use App\Models\Product;
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
        if (!empty($request['id'])) {
            $query->where('id', $request['id']);
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

    public function createVariant($request)
    {
        return Variant::create([
            'product_id' => $request['product_id'],
            'price' => $request['price'],
            'variants_info' => $request['variants_info'],
            'thumbnail' => $request['thumbnail'],
            'images' => $request['images'],
            'created_at' => date('Y-m-d H:i:s'),
            'created_by' => auth()->user()->id,
        ]);
    }

    public function updateVariant($request)
    {
        $variant = Variant::whereNull('deleted_at')->find($request['id']);
        if (!empty($request['product_id'])) {
            $product = Product::whereNull('deleted_at')->where('id', $request['product_id'])->exists();
            if (!$product) {
                return response()->json([
                    'message' => 'Sản phẩm không tồn tại',
                    'status' => false,
                ], 400);
            }
        }
        if (!empty($request['variants_info'])) {
            if (!is_array($request['variants_info'])) {
                return response()->json([
                    'message' => 'Thông tin biến thể sản phẩm phải là mảng',
                    'status' => false,
                ], 400);
            }
        }
        if (!empty($request['images'])) {
            if (!is_array($request['images'])) {
                return response()->json([
                    'message' => 'Danh sách ảnh biến thể sản phẩm phải là mảng',
                    'status' => false,
                ], 400);
            }
        }
        if (!empty($request['price'])) {
            if (!is_numeric($request['price'])) {
                return response()->json([
                    'message' => 'Danh sách ảnh biến thể sản phẩm phải là mảng',
                    'status' => false,
                ], 400);
            }
        }
        try {
            DB::beginTransaction();
            $variant->product_id = $request['product_id'] ?? $variant->product_id;
            $variant->price = $request['price'] ?? $variant->price;
            $variant->variants_info = $request['variants_info'] ?? $variant->variants_info;
            $variant->thumbnail = $request['thumbnail'] ?? $variant->thumbnail;
            $variant->images = $request['images'] ?? $variant->images;
            $variant->updated_at = date('Y-m-d H:i:s');
            $variant->updated_by = auth()->user()->id;
            $variant->save();
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json($e->getMessage(), 400);
        }
        return $variant;
    }

    public function deleteVariant($request)
    {
        $variant = Variant::find($request['id']);
        try {
            DB::beginTransaction();
            $variant->update([
                'deleted_at' => date('Y-m-d H:i:s'),
                'deleted_by' => auth()->user()->id
            ]);
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json($e->getMessage(), 400);
        }
        $variant->save();
        return $variant;
    }
}
