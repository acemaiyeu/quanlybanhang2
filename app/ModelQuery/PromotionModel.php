<?php
namespace App\ModelQuery;

use App\ModelQuery\DiscountModel;
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

        $total_payment = $cart_info[0]->value - $cart->total_discount;
        $cart_info[] = [
            'title' => 'Tổng thanh toán',
            'value' => $total_payment,
            'value_text' => number_format($total_payment, 0, ',', '.') . 'đ',
        ];

        $cart->info_payment = json_encode($cart_info);
        return $cart;
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
