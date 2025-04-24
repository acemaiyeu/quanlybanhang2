<?php
namespace App\ModelQuery;

use App\ModelQuery\DiscountModel;
use App\Models\Promotion;
use App\Models\Variant;
use App\Models\WarehouseDetail;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PromotionModel
{
    public function applyPromotion($cart)
    {
        $cart->total_discount = 0;
        $cart->total_price = $cart->details->sum('total_price');
        $cart_info[0] = [
            'title' => 'Tổng tiền hàng',
            'value' => $cart->total_price,
            'value_text' => number_format($cart->total_price, 0, ',', '.') . 'đ',
        ];

        $cart->info_payment = json_encode($cart_info);
        // Apply Discount
        if (!empty($cart->discount_code)) {
            $cart = DiscountModel::applyDiscountCart($cart);
        }
        if (!empty($cart->fee_ship_code)) {
            $cart = DiscountModel::applyDiscountShipping($cart);
        }

        $cart_info = json_decode($cart->info_payment);

        // ApplyPromotion
        $promotions = Promotion::whereNull('deleted_at')->where('start_date', '<=', Carbon::now('Asia/Ho_Chi_Minh'))->where('end_date', '>=', Carbon::now('Asia/Ho_Chi_Minh'))->where('active', 1)->with('conditions')->get();

        if ($promotions) {
            $gifts = [];
            foreach ($promotions as $promotion) {
                if (self::checkConditions($promotion, $cart)) {
                    // Trả thưởng
                    $total_discount = 0;
                    if ($promotion->apply_for == 'cart' && !empty($promotion->data)) {
                        if ($promotion->data->type == 'discount') {
                            if ($promotion->data->discount_type == 'percent') {
                                $discount_price = $cart->total_price * ($promotion->data->value / 100);
                                if ($discount_price >= $promotion->data->limit) {
                                    $discount_price = $promotion->data->limit;
                                }
                                $total_discount = $discount_price;
                            }
                            if ($promotion->data->discount_type == 'money') {
                                $total_discount = $promotion->data->value;
                            }
                        }
                        if ($promotion->data->type == 'gift' && !empty($promotion->data->gifts)) {
                            foreach ($promotion->data->gifts as $gift) {
                                if (WarehouseDetail::whereNull('deleted_at')
                                        ->where('variant_id', $gift->variant_id)
                                        ->where('quantity', '>=', $gift->quantity)
                                        ->whereHas('variant', function ($query) use ($gift) {
                                            $query->where('id', $gift->variant_id);
                                        })
                                        ->exists()) {
                                    $variant = Variant::whereNull('deleted_at')->select('id', 'thumbnail', 'product_id')->with('product')->find($gift->variant_id);
                                    $gifts[] = [
                                        'promotion_id' => $promotion->id,
                                        'promotion_code' => $promotion->code,
                                        'promotion_name' => $promotion->name,
                                        'variant_id' => $variant->id,
                                        'variant_name' => $variant->product->name,
                                        'quantity' => $gift->quantity,
                                    ];
                                }
                            }
                        }
                    }
                    if ($promotion->apply_for == 'product' && !empty($promotion->data)) {
                        if ($promotion->data->type == 'all_product' && !empty($cart->details)) {  // Giảm giá cho tất cả sản phẩm
                            $total_discount = 0;
                            if ($promotion->data->discount_type == 'percent') {
                                foreach ($cart->details as $detail) {
                                    $discount_price += (($detail->price * $detail->quantity) * ($promotion->data->value / 100));
                                    if ($discount_price >= $promotion->data->limit) {
                                        $discount_price = $promotion->data->limit;
                                    }
                                    if ($discount_price >= $details->total_price) {
                                        $discount_price = $details->total_price;
                                    }
                                    $total_discount += $discount_price;
                                }
                            }
                            if ($promotion->data->discount_type == 'money') {
                                foreach ($cart->details as $detail) {
                                    $discount_price += (($detail->price * $detail->quantity) - $promotion->data->value);
                                    if ($discount_price >= $promotion->data->limit) {
                                        $discount_price = $promotion->data->limit;
                                    }
                                    if ($discount_price >= $details->total_price) {
                                        $discount_price = $details->total_price;
                                    }
                                    $total_discount += $discount_price;
                                }
                            }

                            if ($total_discount > 0) {
                                $cart->total_discount += $discount_price;
                                $cart_info[] = [
                                    'title' => $promotion->name,
                                    'value' => $discount_price,
                                    'value_text' => number_format($discount_price, 0, ',', '.') . 'đ',
                                ];
                            }
                        }
                        if ($promotion->data->type == 'only_product' && !empty($cart->details)) {
                            if ($promotion->data->discount_type == 'percent') {
                                foreach ($cart->details as $detail) {
                                    if ($detail->variant_id === $promotion->data->variant_id) {
                                        $discount_price += (($detail->price * $detail->quantity) * ($promotion->data->value / 100));
                                        if ($discount_price >= $promotion->data->limit) {
                                            $discount_price = $promotion->data->limit;
                                        }
                                        if ($discount_price >= $details->total_price) {
                                            $discount_price = $details->total_price;
                                        }
                                        $total_discount += $discount_price;
                                        break;
                                    }
                                }
                            }
                            if ($promotion->data->discount_type == 'money') {
                                foreach ($cart->details as $detail) {
                                    if ($detail->variant_id === $promotion->data->variant_id) {
                                        $discount_price += (($detail->price * $detail->quantity) - $promotion->data->value);
                                    }
                                }
                            }
                            if ($total_discount > 0) {
                                $cart->total_discount += $discount_price;
                                $cart_info[] = [
                                    'title' => $promotion->name,
                                    'value' => $discount_price,
                                    'value_text' => number_format($discount_price, 0, ',', '.') . 'đ',
                                ];
                            }
                        }
                    }
                }
            }
            if (!empty($gifts)) {
                $cart->gifts = $gifts;
            }
        }

        $total_payment = $cart_info[0]->value - $cart->total_discount;
        $cart_info[] = [
            'title' => 'Tổng thanh toán',
            'value' => $total_payment,
            'value_text' => number_format($total_payment, 0, ',', '.') . 'đ',
        ];

        $cart->info_payment = json_encode($cart_info);
        $cart->save();
        return $cart;
    }

    public function checkConditions($promotion, $cart)
    {
        $next = false;
        if ($promotion->condition_apply === 'SOME') {
            foreach ($promotion->conditions as $condition) {
                if ($condition->condition_apply == 'cart') {
                    if (self::caculatorParallel($cart->total_price, $condition->condition_data->operators, $condition->condition_data->value)) {
                        $next = true;
                        break;
                    }
                }
                if ($condition->condition_apply == 'product') {
                    if ($condition->condition_data->type === 'hava_in_cart') {  // Sản phẩm có trong giỏ hàng
                        foreach ($cart->details as $detail) {
                            if ($detail->variant_id === $condition->condition_data->variant_id && seft::caculatorParallel($detail->quantity, $condition->condition_data->operators, $condition->condition_data->value)) {
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
        if ($promotion->condition_apply === 'ALL') {
            $next = true;

            foreach ($promotion->conditions as $condition) {
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

    public function caculatorParallel($value1, $code, $value2)
    {
        switch ($code) {
            case '+':
                return $value1 + $value2;
            case '-':
                return $value1 - $value2;
            case '*':
                return $value1 * $value2;
            case '/':
                return $value2 != 0 ? $value1 / $value2 : 'Lỗi: chia cho 0';
            case '>':
                return $value1 > $value2;
            case '<':
                return $value1 < $value2;
            case '==':
                return $value1 == $value2;
            case '===':
                return $value1 === $value2;
            default:
                return 'Toán tử không hợp lệ';
        }
    }
}
