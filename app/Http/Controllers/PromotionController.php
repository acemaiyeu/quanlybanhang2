<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreatePromotionValidator;
use App\Http\Requests\DeletePromotionValidator;
use App\Http\Requests\DetailPromotionValidator;
use App\Http\Requests\UpdatePromotionValidator;
use App\ModelQuery\PromotionModel;
use App\Transformers\PromotionTransformer;
use Illuminate\Http\Request;

class PromotionController extends Controller
{
    protected $model;  //

    public function __construct(PromotionModel $model)
    {
        $this->model = $model;
    }

    public function getAllPromotions(Request $request)
    {
        $promotions = $this->model->getPromotions($request);
        return fractal($promotions, new PromotionTransformer())->respond();
    }

    public function createPromotion(CreatePromotionValidator $request)
    {
        $promotion = $this->model->createPromotion($request);
        return fractal($promotion, new PromotionTransformer())->respond();
    }

    public function updatePromotion(UpdatePromotionValidator $request)
    {
        $promotion = $this->model->updatePromotion($request);

        if (empty($promotion->code)) {
            return $promotion;
        }
        return fractal($promotion, new PromotionTransformer())->respond();
    }

    public function deletePromotion(DeletePromotionValidator $request)
    {
        $promotion = $this->model->deletePromotion($request);
        return response()->json(['status' => '200', 'message' => 'success'], 200);
    }

    public function getDetailPromotion(DetailPromotionValidator $request)
    {
        $request['limit'] = 1;
        $promotion = $this->model->getPromotions($request);
        return fractal($promotion, new PromotionTransformer())->respond();
    }
}
