<?php
namespace App\ModelQuery;

use App\ModelQuery\PromotionModel;
use App\Models\CartDetail;
use App\Models\Discount;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DiscountModel
{
    protected $promotionModel;

    public function __construct(PromotionModel $promotionModel)
    {
        $this->promotionModel = $promotionModel;
    }

    public function addDiscount($request, $cart)
    {
        try {
            $discount = Discount::whereNull('deleted_at')->where('start_date', '<=', Carbon::now('Asia/Ho_Chi_Minh'))->where('end_date', '>=', Carbon::now('Asia/Ho_Chi_Minh'))->where('id', $request['discount_id'])->where('active', 1)->first();
            if ($discount) {
                if ($discount->condition_apply === 'cart') {
                    $cart->discount_code = $discount->code;
                } else {
                    CartDetail::whereNull('deleted_at')->where('cart_id', $cart->id)->update([
                        'discount_id' => $discount->id
                    ]);
                }
            }
            return response()->json(['data' => ['status' => 200, 'message' => 'success']], 200);
        } catch (Exception $e) {
            return response()->json(['data' => ['status' => 400, 'message' => $e->getMessage()]], 400);
        }
    }

    public static function applyDiscountCart($cart)
    {
        $info_payment = json_decode($cart->info_payment, true);
        $discount = Discount::whereNull('deleted_at')->where('code', $cart->discount_code)->where('active', 1)->where('start_date', '<=', Carbon::now('Asia/Ho_Chi_Minh'))->where('end_date', '>=', Carbon::now('Asia/Ho_Chi_Minh'))->with('conditions')->first();

        if ($discount) {
            $next = self::checkConditions($discount, $cart);
            // Trả thưởng
            if ($next) {
                if ($discount->apply_for == 'cart' && !empty($discount->data)) {
                    $total_discount = 0;
                    if ($discount->data->type == 'discount') {
                        if ($discount->data->discount_type == 'percent') {
                            $discount_price = $cart->total_price * ($discount->data->value / 100);
                            if ($discount_price >= $discount->data->limit) {
                                $discount_price = $discount->data->limit;
                            }
                            $total_discount = $discount_price;
                        }
                        if ($discount->data->discount_type == 'money') {
                            $total_discount = $discount->data->value;
                        }
                    }
                }
                if ($discount->data->type == 'all_product') {
                    if ($discount->data->discount_type == 'percent') {
                        foreach ($cart->details as $detail) {
                            $discount_price = ($detail->quantity * $detail->price) * ($discount->data->value / 100);
                            if ($discount_price >= $discount->data->limit) {
                                $discount_price = $discount->data->limit;
                            }
                            $total_discount = $discount_price;
                        }
                    }
                    if ($discount->data->discount_type == 'money') {
                        $discount_price = $discount->data->value;
                        if ($discount_price >= ($detail->quantity * $detail->price)) {
                            $discount_price = ($detail->quantity * $detail->price);
                        }
                        $total_discount = $discount_price;
                    }
                }

                if ($total_discount > 0) {
                    $info_payment[] = [
                        'title' => $discount->name,
                        'value' => $total_discount,
                        'value_text' => '-' . number_format($total_discount, 0, ',', '.') . ' đ',
                    ];
                    $cart->total_discount += $total_discount;
                }
            }
        }
        $cart->info_payment = json_encode($info_payment);
        return $cart;
    }

    public static function applyDiscountShipping($cart)
    {
        $info_payment = json_decode($cart->info_payment, true);
        $discount = Discount::whereNull('deleted_at')->where('code', $cart->fee_ship_code)->where('active', 1)->where('start_date', '<=', Carbon::now('Asia/Ho_Chi_Minh'))->where('end_date', '>=', Carbon::now('Asia/Ho_Chi_Minh'))->with('conditions')->first();

        if ($discount) {
            $next = self::checkConditions($discount, $cart);
            // Trả thưởng
            if ($next) {
                $total_discount = 0;
                if ($discount->apply_for == 'shipping' && !empty($discount->data)) {
                    if ($discount->data->discount_type == 'percent') {
                        $discount_price = $fee_ship * ($discount->data->value / 100);
                        if ($discount_price >= $cart->fee_ship) {
                            $discount_price = $cart->fee_ship;
                        }
                        if ($discount_price >= $discount->data->limit) {
                            $discount_price = $discount->data->limit;
                        }
                        $total_discount = $discount_price;
                    }
                    if ($discount->data->discount_type == 'money') {
                        $total_discount = $discount->data->value;
                    }
                }
                if ($total_discount > 0) {
                    $info_payment[] = [
                        'title' => $discount->name,
                        'value' => $total_discount,
                        'value_text' => '-' . number_format($total_discount, 0, ',', '.') . ' đ',
                    ];
                    $cart->total_discount += $total_discount;
                }
            }
        }
        $cart->info_payment = json_encode($info_payment);
        return $cart;
    }

    public static function checkConditions($discount, $cart)
    {
        $next = false;
        if ($discount->condition_apply === 'SOME') {
            foreach ($discount->conditions as $condition) {
                if ($condition->condition_apply == 'cart') {
                    if (self::caculatorParallel($cart->total_price, $condition->condition_data->operators, $condition->condition_data->value)) {
                        $next = true;
                        break;
                    }
                }
                if ($condition->condition_apply == 'product') {
                    if ($condition->condition_data->type === 'hava_in_cart') {  // Sản phẩm có trong giỏ hàng
                        foreach ($cart->details as $detail) {
                            if ($detail->variant_id === $condition->condition_data->value && seft::caculatorParallel($detail->quantity, $condition->condition_data->operators, $condition->condition_data->value)) {
                                $next = true;
                                break;
                            }
                        }
                    }
                    if ($condition->condition_data->type === 'number_product') {  // Tổng số lượng sản phẩm có trong giỏ hàng
                        if (self::caculatorParallel($cart->details->sum('quantity'), $condition->condition_data->operators, $condition->condition_data->value)) {
                            $next = true;
                            break;
                        }
                    }
                }
            }
        }
        if ($discount->condition_apply === 'ALL') {
            $next = true;

            foreach ($discount->conditions as $condition) {
                if ($condition->condition_apply == 'cart') {
                    if (!self::caculatorParallel($cart->total_price, $condition->condition_data->operators, $condition->condition_data->value)) {
                        $next = false;
                        break;
                    }
                }
                if ($condition->condition_apply == 'product') {
                    if ($condition->condition_data->type === 'hava_in_cart') {  // Sản phẩm có trong giỏ hàng
                        foreach ($cart->details as $detail) {
                            if (!$detail->variant_id === $condition->condition_data->value || !seft::caculatorParallel($detail->quantity, $condition->condition_data->operators, $condition->condition_data->value)) {
                                $next = false;
                                break;
                            }
                        }
                    }
                    if ($condition->condition_data->type === 'number_product') {  // Tổng số lượng sản phẩm có trong giỏ hàng
                        if (!self::caculatorParallel($cart->details->sum('quantity'), $condition->condition_data->operators, $condition->condition_data->value)) {
                            $next = false;
                            break;
                        }
                    }
                }
            }
        }
        return $next;
    }

    public static function caculatorParallel($value1, $code, $value2)
    {
        switch ($code) {
            case '<':
                return $value1 < $value2;
            case '<=':
                return $value1 <= $value2;
            case '=':
                return $value1 === $value2;
            case '>=':
                return $value1 >= $value2;
            case '>':
                return $value1 > $value2;
            default:
                return 'Toán tử không hợp lệ';
        }
    }
}
