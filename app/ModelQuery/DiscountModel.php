<?php
namespace App\ModelQuery;

use App\ModelQuery\PromotionModel;
use App\Models\CartDetail;
use App\Models\Discount;
use App\Models\DiscountCondition;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DiscountModel
{
    protected $promotionModel;

    public function __construct(PromotionModel $promotionModel)
    {
        $this->promotionModel = $promotionModel;
    }

    public function getDiscounts($request)
    {
        $query = Discount::query();
        $query->whereNull('deleted_at');

        if (!empty($request['code'])) {
            $query->where('code', $request['code']);
        }
        if (!empty($request['name'])) {
            $query->where('name', 'like', '%' . $request['name'] . '%');
        }
        if (!empty($request['start_date'])) {
            $query->where('start_date', '<=', $request['start_date']);
        }
        if (!empty($request['end_date'])) {
            $query->where('end_date', '>=', $request['end_date']);
        }
        if ($request['active'] !== null) {
            $query->where('active', $request['active']);
        }
        if (!empty($request['sort'])) {
            if (in_array($request['sort'], ['asc', 'desc'])) {
                foreach ($request['sort'] as $key => $value) {
                    $query->orderBy($key, $value);
                }
            }
        }

        $query->with('conditions');
        $limit = $request['limit'] ?? 10;
        return $limit === 1 ? $query->first() : $query->paginate($limit);
    }

    public function createDiscount($request)
    {
        try {
            DB::beginTransaction();
            $discount = new Discount();
            $discount->code = $request['code'];
            $discount->name = $request['name'];
            $discount->start_date = $request['start_date'];
            $discount->end_date = $request['end_date'];
            $discount->active = $request['active'];
            $discount->condition_apply = $request['condition_apply'];
            $discount->apply_for = $request['apply_for'];
            $discount->data = ($request['data']);
            $discount->created_at = Carbon::now('Asia/Ho_Chi_Minh');
            $discount->created_by = auth()->user()->id;
            $discount->save();
            foreach ($request['conditions'] as $detail) {
                $condition = new DiscountCondition();
                $condition->discount_id = $discount->id;
                $condition->condition_apply = $detail['condition_apply'];
                $condition->condition_data = ($detail['condition_data']);
                $condition->created_at = Carbon::now('Asia/Ho_Chi_Minh');
                $condition->created_by = auth()->user()->id;
                $condition->save();
            }

            DB::commit();
            return $discount;
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['data' => ['status' => 400, 'message' => $e->getMessage()]], 400);
        }
    }

    public function updateDiscount($request)
    {
        $discount = Discount::where('code', $request['code'])->first();
        if (!empty($request['condition_apply'])) {
            if ($request['condition_apply'] === 'SOME' && $request['condition_apply'] === 'ALL') {
                return response()->json(['data' => ['status' => 400, 'message' => 'Số lượng Điều kiện áp dụng Khuyến mãi phải là ALL (Tất cả) hoặc SOME (Có ít nhất một)']], 400);
            }
        }
        if (!empty($request['data'])) {
            if (!is_array($request['data'])) {
                return response()->json(['data' => ['status' => 400, 'message' => 'Dữ liệu Áp dụng Khuyến mãi phải là mảng']], 400);
            }
        }
        if (!empty($request['conditions'])) {
            if (!is_array($request['conditions'])) {
                return response()->json(['data' => ['status' => 400, 'message' => 'Điều kiện Áp dụng Khuyến mãi phải là mảng']], 400);
            }
        }
        try {
            DB::beginTransaction();
            $discount->name = $request['name'] ?? $discount->name;
            $discount->start_date = $request['start_date'] ?? $discount->start_date;
            $discount->end_date = $request['end_date'] ?? $discount->end_date;
            $discount->active = $request['active'] ?? $discount->active;
            $discount->condition_apply = $request['condition_apply'] ?? $discount->condition_apply;
            $discount->apply_for = $request['apply_for'] ?? $discount->apply_for;
            $discount->data = $request['data'] ?? $discount->data;
            $discount->updated_at = Carbon::now('Asia/Ho_Chi_Minh') ?? $discount->created_at;
            $discount->updated_by = auth()->user()->id;
            $discount->save();
            if (!empty($request['conditions'])) {
                foreach ($request['conditions'] as $detail) {
                    $condition = DiscountCondition::whereNull('deleted_at')->find($detail['id']);
                    if ($condition) {
                        $condition->discount_id = $detail['discount_id'] ?? $condition->discount_id;
                        $condition->condition_apply = $detail['condition_apply'] ?? $condition->condition_apply;
                        $condition->condition_data = ($detail['condition_data']) ?? $condition->condition_data;
                        $condition->updated_at = Carbon::now('Asia/Ho_Chi_Minh');
                        $condition->updated_by = auth()->user()->id;
                        $condition->save();
                    }
                }
            }

            DB::commit();
            return $discount;
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['data' => ['status' => 400, 'message' => $e->getMessage()]], 400);
        }
    }

    public function deleteDiscount($request)
    {
        $discount = Discount::where('code', $request['code'])->first();

        try {
            DB::beginTransaction();
            $discount->deleted_at = Carbon::now('Asia/Ho_Chi_Minh');
            $discount->deleted_by = auth()->user()->id;

            DB::commit();
            return $discount;
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['data' => ['status' => 400, 'message' => $e->getMessage()]], 400);
        }
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
                $total_discount = 0;
                if ($discount->apply_for == 'cart' && !empty($discount->data)) {
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
                    $discount_price = 0;
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
                        foreach ($cart->details as $detail) {
                            $discount_price += (($detail->price * $detail->quantity) - $discount->data->value);
                            if ($discount_price >= $discount->data->limit) {
                                $discount_price = $discount->data->limit;
                            }
                            if ($discount_price >= $details->total_price) {
                                $discount_price = $details->total_price;
                            }
                            $total_discount += $discount_price;
                        }
                    }
                }
                if ($discount->data->type == 'only_product') {
                    if ($discount->data->discount_type == 'percent') {
                        foreach ($cart->details as $detail) {
                            if ($detail->variant_id != $discount->data->variant_id) {
                                $discount_price = ($detail->quantity * $detail->price) * ($discount->data->value / 100);
                                if ($discount_price >= $discount->data->limit) {
                                    $discount_price = $discount->data->limit;
                                }
                                $total_discount = $discount_price;
                                break;
                            }
                        }
                    }
                    if ($discount->data->discount_type == 'money') {
                        foreach ($cart->details as $detail) {
                            if ($detail->variant_id != $discount->data->variant_id) {
                                $discount_price += (($detail->price * $detail->quantity) - $discount->data->value);
                                if ($discount_price >= $discount->data->limit) {
                                    $discount_price = $discount->data->limit;
                                }
                                if ($discount_price >= $details->total_price) {
                                    $discount_price = $details->total_price;
                                }
                                $total_discount += $discount_price;
                                break;
                            }
                        }
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
