<?php
namespace App\ModelQuery;

use App\Models\Role;
use App\Models\Unit;
use Illuminate\Support\Facades\DB;

class RoleModel
{
    public function getRoles($request)
    {
        $query = Role::query();
        $query->whereNull('deleted_at');

        if (auth()->user()->role->code != 'SUPER_ADMIN') {
            $query->where('code', 'GUEST');
        }

        if (!empty($request['id'])) {
            $query->where('id', $request['id']);
        }
        if (!empty($request['code'])) {
            $query->where('code', $request['code']);
        }
        if (!empty($request['name'])) {
            $query->where('name', 'like', '%' . $request['name'] . '%');
        }

        $limit = $request['limit'] ?? 10;
        return $limit === 1 ? $query->first() : $query->paginate($limit);
    }

    public function createRole($request)
    {
        try {
            DB::beginTransaction();
            $role = new Role();
            $role->code = $request['code'];
            $role->name = $request['name'];
            $role->created_at = date('Y-m-d H:i:s');
            $role->created_by = auth()->user()->id;
            $role->save();

            DB::commit();
        } catch (Exception $e) {
            return response()->json($e->getMessage(), 400);
        }
        return $role;
    }

    public function updateRole($request)
    {
        try {
            DB::beginTransaction();
            $role = Role::whereNull('deleted_at')->where('code', $request['code'])->first();
            $role->name = $request['name'] ?? $role->name;
            $role->created_at = date('Y-m-d H:i:s');
            $role->created_by = auth()->user()->id;
            $role->save();

            DB::commit();
            return $role;
        } catch (Exception $e) {
            return response()->json($e->getMessage(), 400);
        }
    }

    public function deleteRole($request)
    {
        try {
            DB::beginTransaction();
            $role = Role::whereNull('deleted_at')->where('code', $request['code'])->first();

            $role->deleted_at = date('Y-m-d H:i:s');
            $role->deleted_by = auth()->user()->id;
            $role->save();

            DB::commit();
            return $role;
        } catch (Exception $e) {
            return response()->json($e->getMessage(), 400);
        }
    }
}
