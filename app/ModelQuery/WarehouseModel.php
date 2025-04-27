<?php
namespace App\ModelQuery;

use App\Models\Product;
use App\Models\Warehouse;
use Illuminate\Support\Facades\DB;

class WarehouseModel
{
    public function getWarehouses($request)
    {
        $query = Warehouse::query();
        $query->whereNull('deleted_at');
        if (!empty($request['code'])) {
            $query->where('code', $request['code']);
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
        $query->with('distributor');
        $limit = $request['limit'] ?? 10;
        return $limit == 1 ? $query->first() : $query->paginate($limit);
    }

    public function createWarehouse($request)
    {
        return Warehouse::create([
            'code' => $request['code'],
            'name' => $request['name'],
            'user_id' => $request['distributor_id'],
            'address' => $request['address'],
            'lat' => $request['lat'] ?? '',
            'long' => $request['long'] ?? '',
            'created_at' => date('Y-m-d H:i:s'),
            'created_by' => auth()->user()->id,
        ]);
    }

    public function updateWarehouse($request)
    {
        $warehouse = Warehouse::whereNull('deleted_at')->where('code', ($request['code']))->first();

        try {
            DB::beginTransaction();
            $warehouse->code = $request['code'] ?? $warehouse->code;
            $warehouse->name = $request['name'] ?? $warehouse->name;
            $warehouse->user_id = $request['distributor_id'] ?? $warehouse->distributor_id;
            $warehouse->address = $request['address'] ?? $warehouse->address;
            $warehouse->lat = $request['lat'] ?? $warehouse->lat;
            $warehouse->long = $request['lat'] ?? $warehouse->long;
            $warehouse->updated_at = date('Y-m-d H:i:s');
            $warehouse->updated_by = auth()->user()->id;
            $warehouse->save();
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json($e->getMessage(), 400);
        }
        return $warehouse;
    }

    public function deleteWarehouse($request)
    {
        $warehouse = Warehouse::whereNull('deleted_at')->where('code', ($request['code']))->first();
        try {
            DB::beginTransaction();
            $warehouse->update([
                'deleted_at' => date('Y-m-d H:i:s'),
                'deleted_by' => auth()->user()->id
            ]);
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json($e->getMessage(), 400);
        }
        $warehouse->save();
        return $warehouse;
    }
}
