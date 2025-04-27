<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateWarehouseDetailValidator;
use App\Http\Requests\DeleteWarehouseDetailValidator;
use App\Http\Requests\DetailWarehouseDetailValidator;
use App\Http\Requests\UpdateWarehouseDetailValidator;
use App\ModelQuery\WarehouseDetailModel;
use App\Models\WarehouseDetail;
use App\Transformers\WarehouseDetailTransformer;
use Illuminate\Http\Request;

class WarehouseDetailController extends Controller
{
    protected $model;

    public function __construct(WarehouseDetailModel $model)
    {
        $this->model = $model;
    }

    public function getDetailWarehouseDetail(DetailWarehouseDetailValidator $request, $id)
    {
        $request['id'] = $id;
        $warehouse_detail = $this->model->getWarehouseDetails($request);

        return fractal($warehouse_detail, new WarehouseDetailTransformer())->respond();
    }

    public function getAllWarehouseDetails(Request $request)
    {
        $warehouse_detail = $this->model->getWarehouseDetails($request);
        return fractal($warehouse_detail, new WarehouseDetailTransformer())->respond();
    }

    public function createWarehouseDetail(CreateWarehouseDetailValidator $request)
    {
        $warehouse_detail = $this->model->createWarehouseDetail($request);

        return fractal($warehouse_detail, new WarehouseDetailTransformer())->respond();
    }

    public function updateWarehouseDetail(UpdateWarehouseDetailValidator $request)
    {
        $warehouse_detail = $this->model->updateWarehouseDetail($request);
        return fractal($warehouse_detail, new WarehouseDetailTransformer())->respond();
    }

    public function deleteWarehouseDetail($id)
    {
        $warehouse_detail = WarehouseDetail::whereNull('deleted_at')->where('id', $id)->first();
        if (!$warehouse_detail) {
            return response()->json(['errors' => ['status' => '422', 'id' => 'ID Chi tiết Kho hàng không tồn tại'], 'message' => 'Validation Failed'], 422);
        }
        $warehouse_detail = $this->model->deleteWarehouseDetail($warehouse_detail);
        return response()->json(['status' => '200', 'message' => 'success'], 200);
    }
}
