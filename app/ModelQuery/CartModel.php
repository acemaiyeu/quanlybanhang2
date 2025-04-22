<?php
namespace App\ModelQuery;

use App\Models\Cart;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CartModel
{
    public static function getCart($request)
    {
        $cart = Cart::where('session_id', $request['session_id'])->first();
        if (!empty(auth()->user())) {
            $cart = Cart::where('user_id', auth()->user()->id)->first();
        }

        return $cart;
    }

    public static function addToCart($request, $variant)
    {
        try {
            DB::beginTransaction();
            $cart = Cart::where('session_id', $request['session_id'])->first();
            if (!empty(auth()->user())) {
                $cart = Cart::where('user_id', auth()->user()->id)->first();
            }

            if (empty($cart)) {
                $cart = new Cart();
            }
            if (!empty(auth()->user())) {
                $cart->user_id = auth()->user()->id;
            }
            $cart->session_id = $request['session_id'];
            $cart->quantity = 1;
            $cart->save();

            $cart_detail = CartDetail::whereNull('deleted_at')->where('cart_id', $cart->id)->where('variant_id')->first();
            if (empty($cart->details)) {
                $cart_detail = new CartDetail();
                $cart_detail->cart_id = $cart->id;
                $cart_detail->variant_id = $variant->id;
                $cart_detail->price = $variant->price;
                $cart_detail->quantity = 1;
                $cart_detail->total_price = $variant->price;
            } else {
                if ($request['quantity'] > 0) {
                    $cart_detail->price = $variant->price;
                    $cart_detail->quantity += 1;
                    $cart_detail->total_price = $cart_detail->price * $cart_detail->quantity;
                }
                if ($request['quantity'] === 0) {
                    $cart_detail->deleted_at = Carbon::now();
                }
            }
            $cart_detail->save();

            if ($cart->details->count() === 0) {
                $cart->deleted_at = Carbon::now();
                $cart->save();
            }
            DB::commit();
            return $cart;
        } catch (Exception $e) {
            DB::rollBack();
            return $e->getMessage();
        }
    }

    public function updateCartInfo($request, $cart)
    {
        try {
            DB::beginTransaction();

            $cart->fullname = $request['fullname'] ?? $cart->fullname;
            $cart->phone = $request['phone'] ?? $cart->phone;
            $cart->address = $request['address'] ?? $cart->address;
            $cart->method_payment = $request['method_payment'] ?? $cart->method_payment;
            $cart->note = $request['note'] ?? $cart->note;
            $cart->save();

            DB::commit();
            return $cart;
        } catch (Exception $e) {
            DB::rollBack();
            return $e->getMessage();
        }
    }
}
