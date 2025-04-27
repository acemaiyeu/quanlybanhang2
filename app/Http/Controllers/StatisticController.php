<?php

namespace App\Http\Controllers;

use App\Models\Discount;
use Illuminate\Http\Request;

class StatisticController extends Controller
{
    public function getStatisticDiscounts(Request $request)
    {
        // tổng sl đã khuyến mãi
        $count_discount_query = Discount::query();
        $count_discount_query->whereNull('deleted_at');
        if (!empty($request['start_date'])) {
            $count_discount_query->where('start_date', '>=', $request['start_date']);
        }
        if (!empty($request['end_date'])) {
            $count_discount_query->where('end_date', '<=', $request['end_date']);
        }
        if ($request['active'] !== null) {
            $count_discount_query->where('active', $request['active']);
        }
        $count_discount_query->whereHas('orders', function ($q) {
            $q
                ->whereNull('deleted_at')
                ->whereNotNull('discount_code')
                ->whereIn('discount_code', function ($sub) {
                    $sub
                        ->select('code')
                        ->from('discounts')
                        ->whereNull('deleted_at');  // nếu bảng discounts có soft delete
                });
        });
        $discounts = $count_discount_query->get();
        if (!$discounts) {
            return [];
        }
        $count_discount = 0;
        $total_discount = 0;
        foreach ($discounts as $discount) {
            foreach ($discount->orders as $order) {
                $count_discount += 1;
                foreach (json_decode($order->info_payment, 1) as $info) {
                    if (!empty($info['code'])) {
                        $info['code'] == $discount->code ? ($total_discount += $info['value']) : '';
                    }
                }
            }
        }

        // $count_discount  Tổng số lượng sử dụng Mã giảm giá
        // $total_discount Tổng tiền sử dụng Mã giảm giá
        // percent_used Tỉ lệ sử dụng Mã giảm giá
        return ['count' => $count_discount, 'total_discount' => $total_discount];
    }
}
