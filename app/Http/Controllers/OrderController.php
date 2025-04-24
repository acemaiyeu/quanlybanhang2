<?php

namespace App\Http\Controllers;

use App\Http\Requests\ConfirmOrderValidator;
use App\ModelQuery\CartModel;
use App\ModelQuery\OrderModel;
use App\Transformers\OrderTransformer;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    protected $model;
    protected $cartModel;

    public function __construct(OrderModel $model, CartModel $cartModel)
    {
        $this->model = $model;
        $this->cartModel = $cartModel;
    }

    public function confirmOrder(ConfirmOrderValidator $request)
    {
        $cart = $this->cartModel->getCart($request);
        return $this->model->createOrder($request, $cart);
    }

    public function getMyOrders(Request $request)
    {
        $orders = $this->model->getMyOrders($request);
        // return $orders;

        return fractal($orders, new OrderTransformer())->respond();
    }
}
