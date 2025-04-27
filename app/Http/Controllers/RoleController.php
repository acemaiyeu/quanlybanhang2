<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateRoleValidator;
use App\Http\Requests\DeleteRoleValidator;
use App\Http\Requests\DetailRoleValidator;
use App\Http\Requests\UpdateRoleValidator;
use App\ModelQuery\RoleModel;
use App\Transformers\RoleTransformer;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    protected $model;  //

    public function __construct(RoleModel $model)
    {
        $this->model = $model;
    }

    public function getAllRoles(Request $request)
    {
        $roles = $this->model->getRoles($request);
        return fractal($roles, new RoleTransformer())->respond();
    }

    public function createRole(CreateRoleValidator $request)
    {
        $role = $this->model->createRole($request);
        return fractal($role, new RoleTransformer())->respond();
    }

    public function updateRole(UpdateRoleValidator $request)
    {
        $role = $this->model->updateRole($request);

        if (empty($role->code)) {
            return $role;
        }
        return fractal($role, new RoleTransformer())->respond();
    }

    public function deleteRole(DeleteRoleValidator $request)
    {
        $role = $this->model->deleteRole($request);
        return response()->json(['status' => '200', 'message' => 'success'], 200);
    }

    public function getDetailRole(DetailRoleValidator $request)
    {
        $request['limit'] = 1;
        $role = $this->model->getRoles($request);
        return fractal($role, new RoleTransformer())->respond();
    }
}
