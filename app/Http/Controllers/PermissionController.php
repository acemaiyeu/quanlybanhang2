<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreatePermissionValidator;
use App\Http\Requests\DeletePermissionValidator;
use App\Http\Requests\DetailPermissionValidator;
use App\Http\Requests\UpdatePermissionValidator;
use App\ModelQuery\PermissionModel;
use App\Transformers\PermissionTransformer;
use Illuminate\Http\Request;

class PermissionController extends Controller
{
    protected $model;  //

    public function __construct(PermissionModel $model)
    {
        $this->model = $model;
    }

    public function getAllPermissions(Request $request)
    {
        $permissions = $this->model->getPermissions($request);
        return fractal($permissions, new PermissionTransformer())->respond();
    }

    public function createPermission(CreatePermissionValidator $request)
    {
        $permission = $this->model->createPermission($request);
        return fractal($permission, new PermissionTransformer())->respond();
    }

    public function updatePermission(UpdatePermissionValidator $request)
    {
        $permission = $this->model->updatePermission($request);

        if (empty($permission->code)) {
            return $permission;
        }
        return fractal($permission, new PermissionTransformer())->respond();
    }

    public function deletePermission(DeletePermissionValidator $request)
    {
        $permission = $this->model->deletePermission($request);
        return response()->json(['status' => '200', 'message' => 'success'], 200);
    }

    public function getDetailPermission(DetailPermissionValidator $request)
    {
        $request['limit'] = 1;
        $permission = $this->model->getPermissions($request);
        return fractal($permission, new PermissionTransformer())->respond();
    }
}
