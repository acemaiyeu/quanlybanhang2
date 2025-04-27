<?php
namespace App\ModelQuery;

use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AccountModel
{
    public function getAccounts($request)
    {
        $query = User::query();
        $query->whereNull('deleted_at');
        if (auth()->user()->role->code != 'SUPER_ADMIN') {
            $query->whereHas('role', function ($query) use ($request) {
                $query->where('code', 'GUEST');
            });
        }
        if (!empty($request['email'])) {
            $query->where('email', 'like', '%' . $request['email'] . '%');
        }
        if (!empty($request['fullname'])) {
            $query->where('fullname', 'like', '%' . $request['fullname'] . '%');
        }
        if (!empty($request['city_code'])) {
            $query->whereHas('city', function ($query) use ($request) {
                $query->where('code', $request['city_code']);
            });
        }
        if (!empty($request['city_name'])) {
            $query->whereHas('city', function ($query) use ($request) {
                $query->where('name', 'like', '%' . $request['city_name'] . '%');
            });
        }
        if (!empty($request['district_code'])) {
            $query->whereHas('district', function ($query) use ($request) {
                $query->where('code', $request['district_code']);
            });
        }
        if (!empty($request['district_name'])) {
            $query->whereHas('district', function ($query) use ($request) {
                $query->where('name', 'like', '%' . $request['district_name'] . '%');
            });
        }

        if (!empty($request['ward_code'])) {
            $query->whereHas('ward', function ($query) use ($request) {
                $query->where('code', $request['ward_code']);
            });
        }
        if (!empty($request['ward_name'])) {
            $query->whereHas('ward', function ($query) use ($request) {
                $query->where('name', 'like', '%' . $request['ward_name'] . '%');
            });
        }
        if (!empty($request['role_code'])) {
            $query->whereHas('role', function ($query) use ($request) {
                $query->where('code', $request['role_code']);
            });
        }
        if (!empty($request['role_name'])) {
            $query->whereHas('role', function ($query) use ($request) {
                $query->where('name', 'like', '%' . $request['role_name'] . '%');
            });
        }
        if (!empty($request['phone'])) {
            $query->where('phone', $request['phone']);
        }

        if (!empty($request['id'])) {
            $query->where('id', $request['id']);
        }

        if (!empty($request['sort'])) {
            foreach ($request['sort'] as $key => $value) {
                $query->orderBy($key, $value);
            }
        }
        $query->with('role', 'city', 'district', 'ward');
        $limit = $request['limit'] ?? 10;
        return $limit == 1 ? $query->first() : $query->paginate($limit);
    }

    public function createAccount($request)
    {
        try {
            DB::beginTransaction();
            $user = new User();
            $user->email = $request['email'];
            $user->fullname = $request['fullname'] ?? '';
            $user->phone = $request['phone'] ?? '';
            $user->password = Hash::make($request['password']);
            $user->city_id = $request['city_id'] ?? null;
            $user->district_id = $request['district_id'] ?? null;
            $user->ward_id = $request['ward_id'] ?? null;
            $user->role_id = $request['role_id'];
            $user->created_at = date('Y-m-d H:i:s');
            $user->created_by = auth()->user()->id;
            $user->save();
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json($e->getMessage(), 400);
        }
        return $user;
    }

    public function updateAccount($request)
    {
        $user = User::whereNull('deleted_at')->where('id', ($request['id']))->first();

        try {
            DB::beginTransaction();
            $user->email = $request['email'] ?? $user->email;
            $user->fullname = $request['fullname'] ?? $user->fullname;
            $user->phone = $request['phone'] ?? $user->phone;
            $user->city_id = $request['city_id'] ?? $user->city_id;
            $user->district_id = $request['district_id'] ?? $user->district_id;
            $user->ward_id = $request['ward_id'] ?? $user->ward_id;
            $user->role_id = $request['role_id'] ?? $user->role_id;
            $user->updated_at = date('Y-m-d H:i:s');
            $user->updated_by = auth()->user()->id;
            $user->save();
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json($e->getMessage(), 400);
        }
        return $user;
    }

    public function deleteAccount($user)
    {
        try {
            DB::beginTransaction();
            $user->update([
                'deleted_at' => date('Y-m-d H:i:s'),
                'deleted_by' => auth()->user()->id
            ]);
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json($e->getMessage(), 400);
        }
        $user->save();
        return $user;
    }

    public function changePasswordAccount($request)
    {
        $user = User::whereNull('deleted_at')->where('email', ($request['email']))->first();
        try {
            DB::beginTransaction();
            $user->update([
                'password' => Hash::make($request['password']),
                'deleted_at' => date('Y-m-d H:i:s'),
                'deleted_by' => auth()->user()->id
            ]);
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json($e->getMessage(), 400);
        }
        $user->save();
        return $user;
    }
}
