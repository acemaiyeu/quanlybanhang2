<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateWarehouseValidator;
use App\Http\Requests\DeleteWarehouseValidator;
use App\Http\Requests\DetailWarehouseValidator;
use App\Http\Requests\UpdateWarehouseValidator;
use App\ModelQuery\WarehouseModel;
use App\Models\Warehouse;
use App\Transformers\WarehouseTransformer;
use Illuminate\Http\Request;

class WarehouseController extends Controller
{
    protected $model;

    public function __construct(WarehouseModel $model)
    {
        $this->model = $model;
    }

    public function detailWarehouse(Request $request, $id)
    {
        $request['id'] = $id;
        $Warehouse = $this->model->getWarehouses($request);

        return fractal($Warehouse, new WarehouseTransformer())->respond();
    }

    public function getAllWarehouses(Request $request)
    {
        $Warehouse = $this->model->getWarehouses($request);
        return fractal($Warehouse, new WarehouseTransformer())->respond();
    }

    public function createWarehouse(CreateWarehouseValidator $request)
    {
        $Warehouse = $this->model->createWarehouse($request);

        return fractal($Warehouse, new WarehouseTransformer())->respond();
    }

    public function updateWarehouse(UpdateWarehouseValidator $request)
    {
        $warehouse = $this->model->updateWarehouse($request);
        return fractal($warehouse, new WarehouseTransformer())->respond();
    }

    public function deleteWarehouse($code)
    {
        $warehouse = Warehouse::whereNull('deleted_at')->where('code', $code)->first();
        if (!$warehouse) {
            return response()->json(['errors' => ['status' => '422', 'code' => 'Mã Kho hàng không tồn tại'], 'message' => 'Validation Failed'], 422);
        }
        $warehouse = $this->model->deleteWarehouse($warehouse);
        return response()->json(['status' => '200', 'message' => 'success'], 200);
    }

    public function getDetailWarehouse(DetailWarehouseValidator $request)
    {
        $request['limit'] = 1;
        $Warehouse = $this->model->getWarehouses($request);
        return fractal($Warehouse, new WarehouseTransformer())->respond();
    }
}
