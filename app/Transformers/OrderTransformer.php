<?php

namespace App\Transformers;

use App\Models\Order;
use Carbon\Carbon;
use League\Fractal\TransformerAbstract;

class OrderTransformer extends TransformerAbstract
{
    public function transform(Order $order)
    {
        return [
            'id' => $order->id,
            'session_id' => $order->session_id,
            'status' => $order->status->name,
            'fullname' => $order->fullname ?? '',
            'user_phone' => $order->user_phone ?? '',
            'user_address' => $order->user_address ?? '',
            'discount_code' => $order->discount_code ?? '',
            'total_discount' => $order->total_discount ?? 0,
            'fee_ship_code' => $order->fee_ship_code ?? '',
            'fee_ship' => $order->fee_ship ?? 0,
            'gifts' => $order->gifts ?? '',
            'method_payment' => $order->method_payment ?? 'COD',
            'note' => $order->note,
            'total_price' => $order->total_price,
            'info_payment' => !empty($order->info_payment) ? json_decode($order->info_payment) : [],
            'details' => $order->details ?? [],
            'created_at' => Carbon::parse($order->created_at)->format('d/m/Y H:i:s'),
        ];
    }
}
