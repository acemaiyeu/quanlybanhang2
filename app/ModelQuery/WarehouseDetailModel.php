<?php
namespace App\ModelQuery;

use App\Models\Product;
use App\Models\WarehouseDetail;
use Illuminate\Support\Facades\DB;

class WarehouseDetailModel
{
    public function getWarehouseDetails($request)
    {
        $query = WarehouseDetail::query();
        $query->whereNull('deleted_at');
        if (!empty($request['code'])) {
            $query->where('code', $request['product_name']);
        }
        if (!empty($request['name'])) {
            $query->where('name', 'like', '%' . $request['name'] . '%');
        }
        if (!empty($request['distributor_name'])) {
            $query->whereHas('distributor', function ($query) use ($request) {
                $query->where('fullname', 'like', '%' . $request['distributor_name'] . '%');
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
        $query->with('warehouse', 'variant');
        $limit = $request['limit'] ?? 10;
        return $limit == 1 ? $query->first() : $query->paginate($limit);
    }

    public function createWarehouseDetail($request)
    {
        return WarehouseDetail::create([
            'variant_id' => $request['variant_id'],
            'warehouse_id' => $request['warehouse_id'],
            'quantity' => $request['quantity'],
            'created_at' => date('Y-m-d H:i:s'),
            'created_by' => auth()->user()->id,
        ]);
    }

    public function updateWarehouseDetail($request)
    {
        $warehouse_detail = WarehouseDetail::whereNull('deleted_at')->where('id', ($request['id']))->first();

        try {
            DB::beginTransaction();
            $warehouse_detail->variant_id = $request['variant_id'] ?? $warehouse_detail->variant_id;
            $warehouse_detail->warehouse_id = $request['warehouse_id'] ?? $warehouse_detail->nawarehouse_idme;
            $warehouse_detail->quantity = $request['quantity'] ?? $warehouse_detail->quantity;
            $warehouse_detail->updated_at = date('Y-m-d H:i:s');
            $warehouse_detail->updated_by = auth()->user()->id;
            $warehouse_detail->save();
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json($e->getMessage(), 400);
        }
        return $warehouse_detail;
    }

    public function deleteWarehouseDetail($request)
    {
        $warehouse_detail = WarehouseDetail::whereNull('deleted_at')->where('id', ($request['id']))->first();
        try {
            DB::beginTransaction();
            $warehouse_detail->update([
                'deleted_at' => date('Y-m-d H:i:s'),
                'deleted_by' => auth()->user()->id
            ]);
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json($e->getMessage(), 400);
        }
        $warehouse_detail->save();
        return $warehouse_detail;
    }
}
