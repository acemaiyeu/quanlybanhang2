<?php
namespace App\ModelQuery;

use App\ModelQuery\PromotionModel;
use App\Models\Promotion;
use App\Models\PromotionCondition;
use App\Models\Variant;
use App\Models\WarehouseDetail;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PromotionModel
{
    public function getPromotions($request)
    {
        $query = Promotion::query();
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

    public function createPromotion($request)
    {
        try {
            DB::beginTransaction();
            $promotion = new Promotion();
            $promotion->code = $request['code'];
            $promotion->name = $request['name'];
            $promotion->start_date = $request['start_date'];
            $promotion->end_date = $request['end_date'];
            $promotion->active = $request['active'];
            $promotion->condition_apply = $request['condition_apply'];
            $promotion->apply_for = $request['apply_for'];
            $promotion->data = ($request['data']);
            $promotion->created_at = Carbon::now('Asia/Ho_Chi_Minh');
            $promotion->created_by = auth()->user()->id;
            $promotion->save();
            foreach ($request['conditions'] as $detail) {
                $condition = new PromotionCondition();
                $condition->Promotion_id = $promotion->id;
                $condition->condition_apply = $detail['condition_apply'];
                $condition->condition_data = ($detail['condition_data']);
                $condition->created_at = Carbon::now('Asia/Ho_Chi_Minh');
                $condition->created_by = auth()->user()->id;
                $condition->save();
            }

            DB::commit();
            return $promotion;
        } catch (Exception $e) {
            dd($e);
            DB::rollBack();
            return response()->json(['data' => ['status' => 400, 'message' => $e->getMessage()]], 400);
        }
    }

    public function updatePromotion($request)
    {
        $promotion = Promotion::where('code', $request['code'])->first();
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
            $promotion->name = $request['name'] ?? $promotion->name;
            $promotion->start_date = $request['start_date'] ?? $promotion->start_date;
            $promotion->end_date = $request['end_date'] ?? $promotion->end_date;
            $promotion->active = $request['active'] ?? $promotion->active;
            $promotion->condition_apply = $request['condition_apply'] ?? $promotion->condition_apply;
            $promotion->apply_for = $request['apply_for'] ?? $promotion->apply_for;
            $promotion->data = ($request['data']) ?? $promotion->data;
            $promotion->updated_at = Carbon::now('Asia/Ho_Chi_Minh') ?? $promotion->created_at;
            $promotion->updated_by = auth()->user()->id;
            $promotion->save();
            if (!empty($request['conditions'])) {
                foreach ($request['conditions'] as $detail) {
                    $condition = PromotionCondition::whereNull('deleted_at')->find($detail['id']);
                    if ($condition) {
                        $condition->condition_apply = $detail['condition_apply'] ?? $condition->condition_apply;
                        $condition->condition_data = ($detail['condition_data']) ?? $condition->condition_data;
                        $condition->updated_at = Carbon::now('Asia/Ho_Chi_Minh');
                        $condition->updated_by = auth()->user()->id;
                        $condition->save();
                    }
                }
            }

            DB::commit();
            return $promotion;
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['data' => ['status' => 400, 'message' => $e->getMessage()]], 400);
        }
    }

    public function deletePromotion($request)
    {
        $promotion = Promotion::where('code', $request['code'])->first();

        try {
            DB::beginTransaction();
            $promotion->deleted_at = Carbon::now('Asia/Ho_Chi_Minh');
            $promotion->deleted_by = auth()->user()->id;

            DB::commit();
            return $promotion;
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['data' => ['status' => 400, 'message' => $e->getMessage()]], 400);
        }
    }

    public function applyPromotion($cart)
    {
        $cart->total_Promotion = 0;
        $cart->total_price = $cart->details->sum('total_price');
        $cart_info[0] = [
            'title' => 'Tổng tiền hàng',
            'value' => $cart->total_price,
            'value_text' => number_format($cart->total_price, 0, ',', '.') . 'đ',
        ];

        $cart->info_payment = json_encode($cart_info);
        // Apply Promotion
        if (!empty($cart->Promotion_code)) {
            $cart = PromotionModel::applyPromotionCart($cart);
        }
        if (!empty($cart->fee_ship_code)) {
            $cart = PromotionModel::applyPromotionShipping($cart);
        }

        $cart_info = json_decode($cart->info_payment);

        // ApplyPromotion
        $promotions = Promotion::whereNull('deleted_at')->where('start_date', '<=', Carbon::now('Asia/Ho_Chi_Minh'))->where('end_date', '>=', Carbon::now('Asia/Ho_Chi_Minh'))->where('active', 1)->with('conditions')->get();

        if ($promotions) {
            $gifts = [];
            foreach ($promotions as $promotion) {
                if (self::checkConditions($promotion, $cart)) {
                    // Trả thưởng
                    $total_Promotion = 0;
                    if ($promotion->apply_for == 'cart' && !empty($promotion->data)) {
                        if ($promotion->data->type == 'Promotion') {
                            if ($promotion->data->Promotion_type == 'percent') {
                                $promotion_price = $cart->total_price * ($promotion->data->value / 100);
                                if ($promotion_price >= $promotion->data->limit) {
                                    $promotion_price = $promotion->data->limit;
                                }
                                $total_Promotion = $promotion_price;
                            }
                            if ($promotion->data->Promotion_type == 'money') {
                                $total_Promotion = $promotion->data->value;
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
                            $total_Promotion = 0;
                            $promotion_price = 0;
                            if ($promotion->data->Promotion_type == 'percent') {
                                foreach ($cart->details as $detail) {
                                    $promotion_price += (($detail->price * $detail->quantity) * ($promotion->data->value / 100));
                                    if ($promotion_price >= $promotion->data->limit) {
                                        $promotion_price = $promotion->data->limit;
                                    }
                                    if ($promotion_price >= $detail->total_price) {
                                        $promotion_price = $detail->total_price;
                                    }
                                    $total_Promotion += $promotion_price;
                                }
                            }
                            if ($promotion->data->Promotion_type == 'money') {
                                foreach ($cart->details as $detail) {
                                    $promotion_price += (($detail->price * $detail->quantity) - $promotion->data->value);
                                    if ($promotion_price >= $promotion->data->limit) {
                                        $promotion_price = $promotion->data->limit;
                                    }
                                    if ($promotion_price >= $details->total_price) {
                                        $promotion_price = $details->total_price;
                                    }
                                    $total_Promotion += $promotion_price;
                                }
                            }

                            if ($total_Promotion > 0) {
                                $cart->total_Promotion += $promotion_price;
                                $cart_info[] = [
                                    'title' => $promotion->name,
                                    'code' => $promotion->code,
                                    'value' => $promotion_price,
                                    'value_text' => number_format($promotion_price, 0, ',', '.') . 'đ',
                                ];
                            }
                        }
                        if ($promotion->data->type == 'only_product' && !empty($cart->details)) {
                            if ($promotion->data->Promotion_type == 'percent') {
                                foreach ($cart->details as $detail) {
                                    if ($detail->variant_id === $promotion->data->variant_id) {
                                        $promotion_price += (($detail->price * $detail->quantity) * ($promotion->data->value / 100));
                                        if ($promotion_price >= $promotion->data->limit) {
                                            $promotion_price = $promotion->data->limit;
                                        }
                                        if ($promotion_price >= $details->total_price) {
                                            $promotion_price = $details->total_price;
                                        }
                                        $total_Promotion += $promotion_price;
                                        break;
                                    }
                                }
                            }
                            if ($promotion->data->Promotion_type == 'money') {
                                foreach ($cart->details as $detail) {
                                    if ($detail->variant_id === $promotion->data->variant_id) {
                                        $promotion_price += (($detail->price * $detail->quantity) - $promotion->data->value);
                                    }
                                }
                            }
                            if ($total_Promotion > 0) {
                                $cart->total_Promotion += $promotion_price;
                                $cart_info[] = [
                                    'title' => $promotion->name,
                                    'code' => $promotion->code,
                                    'value' => $promotion_price,
                                    'value_text' => number_format($promotion_price, 0, ',', '.') . 'đ',
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

        $total_payment = $cart_info[0]->value - $cart->total_Promotion;
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
