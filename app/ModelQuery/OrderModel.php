<?php
namespace App\ModelQuery;

use App\Models\Cart;
use App\Models\CartDetail;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\OrderStatus;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderModel
{
    public function getMyOrders($request)
    {
        $query = Order::query();
        $query->whereNull('deleted_at');
        $query->where('user_id', auth()->user()->id);
        if (!empty($request['code'])) {
            $query->where('code', $request['code']);
        }
        if (!empty($request['status'])) {
            $query->whereHas('status', function ($query) use ($request) {
                $query->where('name', $request['status']);
            });
        }
        if (!empty($request['sort'])) {
            if (in_array($request['sort'], ['asc', 'desc'])) {
                foreach ($request['sort'] as $key => $value) {
                    $query->orderBy($key, $value);
                }
            }
        }
        $query->with(['status', 'details']);
        $limit = $request['limit'] ?? 10;
        return $limit === 1 ? $query->first() : $query->paginate($limit);
    }

    public static function getAllOrders($request)
    {
        $query = Order::query();
        $query->whereNull('deleted_at');
        if (!empty($request['user_id'])) {
            $query->where('user_id', $request['user_id']);
        }

        if (!empty($request['status'])) {
            $query->whereHas('status', function ($query) use ($request) {
                $query->where('code', $request['status']);
            });
        }
        if (!empty($request['sort'])) {
            if (in_array($request['sort'], ['asc', 'desc'])) {
                foreach ($request['sort'] as $key => $value) {
                    $query->orderBy($key, $value);
                }
            }
        }
        $limit = $request['limit'] ?? 10;
        return $limit === 1 ? $query->first() : $query->paginate($limit);
    }

    public static function createOrder($request, $cart)
    {
        try {
            DB::beginTransaction();

            $order_status = OrderStatus::whereNull('deleted_at')->where('code', 'PENDING')->first();

            $order = new Order();
            if (!empty(auth()->user())) {
                $order->user_id = auth()->user()->id;
            }
            $order->code = self::generateSecureOrderCode();
            $order->cart_id = $cart->id;
            $order->order_status_id = $order_status->id;
            $order->fullname = $request['fullname'];
            $order->user_phone = $request['user_phone'];
            $order->user_address = $request['user_address'];
            $order->method_payment = $request['method_payment'];
            $order->note = $request['note'] ?? $cart->note;
            $order->discount_code = $cart->discount_code;
            $order->total_discount = $cart->total_discount;
            $order->fee_ship_code = $cart->fee_ship_code;
            $order->fee_ship = $cart->fee_ship;
            $order->total_price = $cart->total_price;
            $order->info_payment = $cart->info_payment;
            $order->save();

            foreach ($cart->details as $detail) {
                $order_detail = new OrderDetail();
                $order_detail->order_id = $order->id;
                $order_detail->variant_id = $detail->variant_id;
                $order_detail->quantity = $detail->quantity;
                $order_detail->price = $detail->price;
                $order_detail->discount_id = $detail->discount_id;
                $order_detail->discount_code = $detail->discount_code;
                $order_detail->discount_price = $detail->discount_price;
                $order_detail->total_discount = $detail->total_discount;
                $order_detail->total_price = $detail->total_price;
                $order_detail->save();
            }
            CartDetail::whereNull('deleted_at')->where('cart_id', $cart->id)->update(['deleted_at' => Carbon::now('Asia/Ho_Chi_Minh')]);
            $cart->deleted_at = Carbon::now('Asia/Ho_Chi_Minh');
            $cart->save();
            DB::commit();
            return $order;
        } catch (Exception $e) {
            DB::rollBack();
            return $e->getMessage();
        }
    }

    public static function updateOrder($request, $order)
    {
        try {
            DB::beginTransaction();
            $order->order_status_id = !empty($request['order_status_id']) ? $request['order_status_id'] : $order->order_status_id;
            $order->fullname = $request['fullname'] ?? $order->fullname;
            $order->user_phone = $request['user_phone'] ?? $order->user_phone;
            $order->user_address = $request['user_address'] ?? $order->user_address;
            $order->note = $request['note'] ?? $order->note;
            $order->save();

            DB::commit();
            return $order;
        } catch (Exception $e) {
            DB::rollBack();
            return $e->getMessage();
        }
    }

    public static function generateSecureOrderCode()
    {
        return 'DH-' . strtoupper(Str::random(10));
    }
}
