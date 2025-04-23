<?php

namespace App\Transformers;

use App\Models\Cart;
use League\Fractal\TransformerAbstract;

class CartTransformer extends TransformerAbstract
{
    /**
     * List of resources to automatically include
     *
     * @var array
     */
    protected array $defaultIncludes = [
        //
    ];

    /**
     * List of resources possible to include
     *
     * @var array
     */
    protected array $availableIncludes = [
        //
    ];

    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public function transform(Cart $cart)
    {
        return [
            'id' => $cart->id,
            'session_id' => $cart->session_id,
            'fullname' => $cart->fullname ?? '',
            'user_phone' => $cart->user_phone ?? '',
            'user_address' => $cart->user_address ?? '',
            'discount_code' => $cart->discount_code ?? '',
            'total_discount' => $cart->total_discount ?? 0,
            'fee_ship_code' => $cart->fee_ship_code ?? '',
            'fee_ship' => $cart->fee_ship ?? 0,
            'method_payment' => $cart->method_payment ?? 'COD',
            'note' => $cart->note,
            'total_price' => $cart->total_price,
            'info_payment' => !empty($cart->info_payment) ? json_decode($cart->info_payment) : [],
            'details' => $cart->details ?? [],
        ];
    }
}
