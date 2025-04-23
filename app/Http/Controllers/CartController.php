<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddDiscountValidator;
use App\Http\Requests\AddToCartValidator;
use App\Http\Requests\GetCartValidator;
use App\ModelQuery\CartModel;
use App\ModelQuery\DiscountModel;
use App\ModelQuery\PromotionModel;
use App\Models\Variant;
use App\Transformers\CartTransformer;
use Illuminate\Http\Request;

class CartController extends Controller
{
    protected $model;
    protected $promotionModel;
    protected $discountModel;

    public function __construct(CartModel $model, PromotionModel $promotionModel, DiscountModel $discountModel)
    {
        $this->model = $model;
        $this->promotionModel = $promotionModel;
        $this->discountModel = $discountModel;
    }

    public function getCart(GetCartValidator $request)
    {
        $cart = $this->model->getCart($request);
        $cart = $this->promotionModel->applyPromotion($cart);
        return fractal($cart, new CartTransformer())->respond();
    }

    public function addToCart(AddToCartValidator $request)
    {
        $variant = Variant::whereNull('deleted_at')->find($request['variant_id']);
        $cart = $this->model->addToCart($request, $variant);
        return response()->json(['status' => '200', 'message' => 'success'], 200);
    }

    public function addDiscount(AddDiscountValidator $request)
    {
        $cart = $this->model->getCart($request);
        return $this->discountModel->addDiscount($request, $cart);
    }
}
