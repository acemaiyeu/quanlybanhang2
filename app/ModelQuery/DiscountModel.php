<?php
namespace App\ModelQuery;

use Illuminate\Support\Facades\DB;

class DiscountModel
{
    public function addDiscount($request, $cart)
    {
        $discount = Discount::whereNull('deleted_at')->where('start_date', '<=', Carbon::now('Asia/Ho_Chi_Minh'))->where('end_date', '>=', Carbon::now('Asia/Ho_Chi_Minh'))->where('code', $reuqest['code'])->where('active', 1)->first();
        if ($discount) {
            if ($discount->condition_apply === 'cart') {
                $cart->discount_code = $discount->code;
            } else {
                $cart->details->update([
                    'discount_id' => $discount->id
                ]);
            }
        }
    }
}
