<?php

namespace App\Http\Controllers;

use App\ModelQuery\VariantModel;
use App\Transformers\VariantTransformer;
use Illuminate\Http\Request;

class VariantController extends Controller
{
    protected $model;

    public function __construct(VariantModel $model)
    {
        $this->model = $model;
    }

    public function detailVariantProduct(Request $request, $id)
    {
        $request['id'] = $id;
        $variant = $this->model->getVariantProduct($request);

        return fractal($variant, new VariantTransformer())->respond();
    }
}
