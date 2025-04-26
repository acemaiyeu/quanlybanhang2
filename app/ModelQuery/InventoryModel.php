<?php
namespace App\ModelQuery;

use App\Models\Inventory;
use App\Models\Order;
use App\Models\Product;
use App\Models\Unit;
use App\Models\Variant;
use App\Models\Warehouse;
use App\Models\WarehouseDetail;
use Illuminate\Support\Facades\DB;

class InventoryModel
{
    public function getInventories($request)
    {
        $query = Inventory::query();
        $query->whereNull('deleted_at');

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
        if (!empty($request['warehouse_code'])) {
            $query->whereHas('warehouse', function ($query) use ($request) {
                $query->where('code', $request['warehouse_code']);
            });
        }
        if (!empty($request['warehouse_name'])) {
            $query->whereHas('warehouse', function ($query) use ($request) {
                $query->where('name', 'like', '%' . $request['warehouse_name'] . '%');
            });
        }
        if ($request['status'] !== null) {
            $query->where('status', $request['status']);
        }
        if (!empty($request['batch_number'])) {
            $query->where('batch_number', $request['batch_number']);
        }
        if (!empty($request['location'])) {
            $query->where('location', 'like', '%' . $request['location'] . '%');
        }
        if (!empty($request['variant_info'])) {
            $query->whereHas('variant', function ($query) use ($request) {
                $query->where('variants_info', 'like', '%' . $request['variant_info'] . '%');
            });
        }
        if (!empty($request['sort'])) {
            if (in_array($request['sort'], ['asc', 'desc'])) {
                foreach ($request['sort'] as $key => $value) {
                    $query->orderBy($key, $value);
                }
            }
        }

        $query->with('product', 'variant', 'warehouse');
        $limit = $request['limit'] ?? 10;
        return $limit === 1 ? $query->first() : $query->paginate($limit);
    }

    public function createInventory($request)
    {
        try {
            if ($request['status'] == 'IMPORT' && empty($request['price'])) {
                return response()->json(['status' => 422, 'message' => 'Giá không được để trống khi nhập kho'], 422);
            }
            if ($request['status'] == 'EXPORT' && empty($request['order_id'])) {
                return response()->json(['status' => 422, 'message' => 'Khi xuất kho bạn phải nhập mã đơn hàng'], 422);
            }
            if ($request['status'] == 'EXPORT') {
                $order = Order::whereNull('deleted_at')->where('id', $request['order_id'])->first();
                if (empty($order->details)) {
                }
                $check = false;
                foreach ($order->details as $detail) {
                    if ($details->variant_id == $request['variant_id']) {
                        $check = true;
                    }
                }
                if (!$check) {
                    return response()->json(['status' => 422, 'message' => 'Không tìm thấy sản phẩm trong đơn hàng để xuất kho'], 422);
                }
            }
            $warehouse_detail = WarehouseDetail::whereNull('deleted_at')->where('warehouse_id', $request['warehouse_id'])->where('variant_id', $request['variant_id'])->first();
            DB::beginTransaction();
            $inventory = new Inventory();
            $inventory->product_id = $request['product_id'];
            $inventory->variant_id = $request['variant_id'];
            $inventory->warehouse_id = $request['warehouse_id'];
            $inventory->unit_id = $request['unit_id'];
            $inventory->order_id = $request['order_id'] ?? null;
            $inventory->quantity = $request['quantity'];
            $inventory->price = $request['price'] ?? 0;
            $inventory->location = $request['location'];
            $inventory->status = $request['status'];
            $inventory->batch_number = $request['batch_number'];
            $inventory->expiration_date = $request['expiration_date'];
            $inventory->notes = $request['notes'];
            $inventory->created_at = date('Y-m-d H:i:s');
            $inventory->created_by = auth()->user()->id;
            $inventory->save();

            if (!$warehouse_detail) {
                if ($request['status'] == 'IMPORT') {
                    $warehouse_detail = new WarehouseDetail();
                    $warehouse_detail->variant_id = $request['variant_id'];
                    $warehouse_detail->warehouse_id = $request['warehouse_id'];
                    $warehouse_detail->quantity = $request['quantity'];
                    $warehouse_detail->created_at = date('Y-m-d H:i:s');
                    $warehouse_detail->created_by = auth()->user()->id;
                    $warehouse_detail->save();
                }
                if ($request['status'] == 'EXPORT') {
                    return response()->json(['status' => 422, 'message' => 'Kho hàng không có sản phẩm để xuất kho'], 422);
                }
            } else {
                if ($request['status'] == 'IMPORT') {
                    $warehouse_detail->quantity += $request['quantity'];
                }
                if ($request['status'] == 'EXPORT') {
                    $warehouse_detail->quantity -= $request['quantity'];
                }

                $warehouse_detail->updated_at = date('Y-m-d H:i:s');
                $warehouse_detail->updated_by = auth()->user()->id;
                $warehouse_detail->save();
            }
            DB::commit();
        } catch (Exception $e) {
            return response()->json($e->getMessage(), 400);
        }
        return $inventory;
    }

    public function updateInventory($request)
    {
        try {
            if ($request['status'] == 'IMPORT' && empty($request['price'])) {
                return response()->json(['status' => 422, 'message' => 'Giá không được để trống khi nhập kho'], 422);
            }
            if ($request['status'] == 'EXPORT' && empty($request['order_id'])) {
                return response()->json(['status' => 422, 'message' => 'Khi xuất kho bạn phải nhập ID đơn hàng'], 422);
            }
            if (!empty($request['product_id'])) {
                $product = Product::whereNull('deleted_at')->where('id', $request['product_id'])->exists();
                if (!$product) {
                    return response()->json(['status' => 422, 'message' => 'ID sản phẩm không tồn tại'], 422);
                }
            }
            if (!empty($request['product_id'])) {
                $product = Product::whereNull('deleted_at')->where('id', $request['product_id'])->exists();
                if (!$product) {
                    return response()->json(['status' => 422, 'message' => 'ID sản phẩm không tồn tại'], 422);
                }
            }
            if (!empty($request['variant_id'])) {
                $product = Variant::whereNull('deleted_at')->where('id', $request['variant_id'])->exists();
                if (!$product) {
                    return response()->json(['status' => 422, 'message' => 'ID sản phẩm không tồn tại'], 422);
                }
            }
            if (!empty($request['unit_id'])) {
                $product = Unit::whereNull('deleted_at')->where('id', $request['unit_id'])->exists();
                if (!$product) {
                    return response()->json(['status' => 422, 'message' => 'ID sản phẩm không tồn tại'], 422);
                }
            }
            if (!empty($request['warehouse_id'])) {
                $product = Warehouse::whereNull('deleted_at')->where('id', $request['warehouse_id'])->exists();
                if (!$product) {
                    return response()->json(['status' => 422, 'message' => 'ID sản phẩm không tồn tại'], 422);
                }
            }
            if (!empty($request['order_id'])) {
                $product = Order::whereNull('deleted_at')->where('id', $request['order_id'])->exists();
                if (!$product) {
                    return response()->json(['status' => 422, 'message' => 'ID sản phẩm không tồn tại'], 422);
                }
                if ($request['status'] == 'EXPORT') {
                    $order = Order::whereNull('deleted_at')->where('id', $request['order_id'])->first();
                    if (empty($order->details)) {
                    }
                    $check = false;
                    foreach ($order->details as $detail) {
                        if ($details->variant_id == $request['variant_id']) {
                            $check = true;
                        }
                    }
                    if (!$check) {
                        return response()->json(['status' => 422, 'message' => 'Không tìm thấy sản phẩm trong đơn hàng để xuất kho'], 422);
                    }
                }
            }
            $warehouse_detail = WarehouseDetail::whereNull('deleted_at')->where('warehouse_id', $request['warehouse_id'])->where('variant_id', $request['variant_id'])->first();
            DB::beginTransaction();
            $inventory = Inventory::whereNull('deleted_at')->find($request['id']);
            $inventory->product_id = $request['product_id'] ?? $inventory->product_id;
            $inventory->variant_id = $request['variant_id'] ?? $inventory->variant_id;
            $inventory->warehouse_id = $request['warehouse_id'] ?? $inventory->warehouse_id;
            $inventory->unit_id = $request['unit_id'] ?? $inventory->unit_id;
            $inventory->order_id = !empty($request['order_id']) ? $request['order_id'] : $inventory->order_id;

            if (!$warehouse_detail) {
                return response()->json(['status' => 422, 'message' => 'Không tìm thấy kho hàng'], 422);
            } else {
                if ($request['status'] == 'IMPORT' && $inventory->quantity != $request['quantity']) {
                    $warehouse_detail->quantity -= $inventory->quantity;
                    $warehouse_detail->quantity += $request['quantity'];
                }
                if ($request['status'] == 'EXPORT' && $inventory->quantity != $request['quantity']) {
                    $warehouse_detail->quantity += $inventory->quantity;
                    $warehouse_detail->quantity -= $request['quantity'];
                }

                $warehouse_detail->updated_at = date('Y-m-d H:i:s');
                $warehouse_detail->updated_by = auth()->user()->id;
                $warehouse_detail->save();
            }
            $inventory->quantity = $request['quantity'] ?? $inventory->quantity;
            $inventory->price = $request['price'] ?? $inventory->price;
            $inventory->location = $request['location'] ?? $inventory->location;
            $inventory->status = $request['status'] ?? $inventory->status;
            $inventory->batch_number = $request['batch_number'] ?? $inventory->batch_number;
            $inventory->expiration_date = $request['expiration_date'] ?? $inventory->expiration_date;
            $inventory->notes = $request['notes'] ?? $inventory->notes;
            $inventory->updated_at = date('Y-m-d H:i:s');
            $inventory->updated_by = auth()->user()->id;
            $inventory->save();
            DB::commit();
        } catch (Exception $e) {
            return response()->json($e->getMessage(), 400);
        }
        return $inventory;
    }

    public function deleteInventory($request)
    {
        try {
            DB::beginTransaction();
            $inventory = Inventory::whereNull('deleted_at')->where('id', $request['id'])->first();

            $inventory->deleted_at = date('Y-m-d H:i:s');
            $inventory->deleted_by = auth()->user()->id;
            $inventory->save();

            DB::commit();
            return $inventory;
        } catch (Exception $e) {
            return response()->json($e->getMessage(), 400);
        }
    }
}
