<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateInventoryValidator;
use App\Http\Requests\DeleteInventoryValidator;
use App\Http\Requests\DetailInventoryValidator;
use App\Http\Requests\UpdateInventoryValidator;
use App\ModelQuery\InventoryModel;
use App\Transformers\InventoryTransformer;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    protected $model;  //

    public function __construct(InventoryModel $model)
    {
        $this->model = $model;
    }

    public function getAllInventories(Request $request)
    {
        $inventories = $this->model->getInventories($request);
        return fractal($inventories, new InventoryTransformer())->respond();
    }

    public function createInventory(CreateInventoryValidator $request)
    {
        $inventory = $this->model->createInventory($request);
        if (empty($inventory->id)) {
            return $inventory;
        }
        return fractal($inventory, new InventoryTransformer())->respond();
    }

    public function updateInventory(UpdateInventoryValidator $request)
    {
        $inventory = $this->model->updateInventory($request);

        if (empty($inventory->code)) {
            return $inventory;
        }
        return fractal($inventory, new InventoryTransformer())->respond();
    }

    public function deleteInventory(DeleteInventoryValidator $request)
    {
        $inventory = $this->model->deleteInventory($request);
        return response()->json(['status' => '200', 'message' => 'success'], 200);
    }

    public function getDetailInventory(DetailInventoryValidator $request)
    {
        $request['limit'] = 1;
        $inventory = $this->model->getInventories($request);
        return fractal($inventory, new InventoryTransformer())->respond();
    }
}
