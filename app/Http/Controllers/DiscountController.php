<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateDiscountValidator;
use App\Http\Requests\DeleteDiscountValidator;
use App\Http\Requests\DetailDiscountValidator;
use App\Http\Requests\UpdateDiscountValidator;
use App\ModelQuery\DiscountModel;
use App\Transformers\DiscountTransformer;
use Illuminate\Http\Request;

class DiscountController extends Controller
{
    protected $model;  //

    public function __construct(DiscountModel $model)
    {
        $this->model = $model;
    }

    public function getAllDiscounts(Request $request)
    {
        $discounts = $this->model->getDiscounts($request);
        return fractal($discounts, new DiscountTransformer())->respond();
    }

    public function createDiscount(CreateDiscountValidator $request)
    {
        $discount = $this->model->createDiscount($request);
        return fractal($discount, new DiscountTransformer())->respond();
    }

    public function updateDiscount(UpdateDiscountValidator $request)
    {
        $discount = $this->model->updateDiscount($request);

        if (empty($discount->code)) {
            return $discount;
        }
        return fractal($discount, new DiscountTransformer())->respond();
    }

    public function deleteDiscount(DeleteDiscountValidator $request)
    {
        $discount = $this->model->deleteDiscount($request);
        return response()->json(['status' => '200', 'message' => 'success'], 200);
    }

    public function getDetailDiscount(DetailDiscountValidator $request)
    {
        $request['limit'] = 1;
        $discount = $this->model->getDiscounts($request);
        return fractal($discount, new DiscountTransformer())->respond();
    }
}
