<?php
namespace App\ModelQuery;

use App\Models\Permission;
use App\Models\Unit;
use Illuminate\Support\Facades\DB;

class PermissionModel
{
    public function getPermissions($request)
    {
        $query = Permission::query();
        $query->whereNull('deleted_at');

        if (auth()->user()->role->code != 'SUPER_ADMIN') {
            $query->where('created_by', -1);
        }
        if (!empty($request['id'])) {
            $query->where('id', $request['id']);
        }
        if (!empty($request['code'])) {
            $query->where('code', $request['code']);
        }
        if (!empty($request['title'])) {
            $query->where('title', 'like', '%' . $request['title'] . '%');
        }

        $query->with('details');
        $limit = $request['limit'] ?? 10;
        return $limit === 1 ? $query->first() : $query->paginate($limit);
    }

    public function createPermission($request)
    {
        try {
            DB::beginTransaction();
            $permission = new Permission();
            $permission->code = $request['code'];
            $permission->title = $request['title'];
            $permission->created_at = date('Y-m-d H:i:s');
            $permission->created_by = auth()->user()->id;
            $permission->save();

            DB::commit();
        } catch (Exception $e) {
            return response()->json($e->getMessage(), 400);
        }
        return $permission;
    }

    public function updatePermission($request)
    {
        try {
            DB::beginTransaction();
            $permission = Permission::whereNull('deleted_at')->where('code', $request['code'])->first();
            $permission->title = $request['title'] ?? $permission->title;
            $permission->created_at = date('Y-m-d H:i:s');
            $permission->created_by = auth()->user()->id;
            $permission->save();

            DB::commit();
            return $permission;
        } catch (Exception $e) {
            return response()->json($e->getMessage(), 400);
        }
    }

    public function deletePermission($request)
    {
        try {
            DB::beginTransaction();
            $permission = Permission::whereNull('deleted_at')->where('code', $request['code'])->first();

            $permission->deleted_at = date('Y-m-d H:i:s');
            $permission->deleted_by = auth()->user()->id;
            $permission->save();

            DB::commit();
            return $permission;
        } catch (Exception $e) {
            return response()->json($e->getMessage(), 400);
        }
    }
}
