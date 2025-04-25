<?php
namespace App\ModelQuery;

use App\Models\Category;
use App\Models\Unit;
use Illuminate\Support\Facades\DB;

class CategoryModel
{
    public function getCategories($request)
    {
        $query = Category::query();
        $query->whereNull('deleted_at');

        if (!empty($request['id'])) {
            $query->where('id', $request['id']);
        }
        if (!empty($request['category_code'])) {
            $query->where('code', $request['category_code']);
        }
        if (!empty($request['category_name'])) {
            $query->where('name', 'like', '%' . $request['category_name'] . '%');
        }
        if (!empty($request['product_code'])) {
            $query->whereHas('product', function ($query) use ($request) {
                $query->where('code', $request['product_code']);
            });
        }
        if (!empty($request['product_name'])) {
            $query->whereHas('product', function ($query) use ($request) {
                $query->where('name', 'like', '%' . $request['product_name'] . '%');
            });
        }

        $query->with('product');
        $limit = $request['limit'] ?? 10;
        return $limit === 1 ? $query->first() : $query->paginate($limit);
    }

    public function createCategory($request)
    {
        try {
            DB::beginTransaction();
            $category = new Category();
            $category->code = $request['code'];
            $category->name = $request['name'];
            $category->created_at = date('Y-m-d H:i:s');
            $category->created_by = auth()->user()->id;
            $category->save();

            DB::commit();
        } catch (Exception $e) {
            return response()->json($e->getMessage(), 400);
        }
        return $category;
    }

    public function updateCategory($request)
    {
        try {
            DB::beginTransaction();
            $category = Category::whereNull('deleted_at')->where('code', $request['code'])->first();
            $category->name = $request['name'] ?? $category->name;
            $category->created_at = date('Y-m-d H:i:s');
            $category->created_by = auth()->user()->id;
            $category->save();

            DB::commit();
            return $category;
        } catch (Exception $e) {
            return response()->json($e->getMessage(), 400);
        }
    }

    public function deleteCategory($request)
    {
        try {
            DB::beginTransaction();
            $category = Category::whereNull('deleted_at')->where('code', $request['code'])->first();

            $category->deleted_at = date('Y-m-d H:i:s');
            $category->deleted_by = auth()->user()->id;
            $category->save();

            DB::commit();
            return $category;
        } catch (Exception $e) {
            return response()->json($e->getMessage(), 400);
        }
    }
}
