<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateVariantValidator;
use App\Http\Requests\DeleteVariantValidator;
use App\Http\Requests\DetailVariantValidator;
use App\Http\Requests\UpdateVariantValidator;
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

    public function getAllVariants(Request $request)
    {
        $variant = $this->model->getVariantProduct($request);
        return fractal($variant, new VariantTransformer())->respond();
    }

    public function createVariant(CreateVariantValidator $request)
    {
        $variant = $this->model->createVariant($request);

        return fractal($variant, new VariantTransformer())->respond();
    }

    public function updateVariant(UpdateVariantValidator $request)
    {
        $variant = $this->model->updateVariant($request);
        if (empty($variant->product_id)) {
            return $variant;
        }
        return fractal($variant, new VariantTransformer())->respond();
    }

    public function deleteVariant(DeleteVariantValidator $request)
    {
        $variant = $this->model->deleteVariant($request);
        return response()->json(['status' => '200', 'message' => 'success'], 200);
    }

    public function getDetailVariant(DetailVariantValidator $request)
    {
        $request['limit'] = 1;
        $variant = $this->model->getVariantProduct($request);
        return fractal($variant, new VariantTransformer())->respond();
    }
}
