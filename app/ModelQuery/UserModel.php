<?php
namespace App\ModelQuery;

use Illuminate\Support\Facades\DB;

class UserModel
{
    protected $table = 'users';

    public function updateUser($request)
    {
        try {
            DB::beginTransaction();
            $user = auth()->user();
            // dd($request['phone'], $user->id);

            $user->username = $request['username'] ?? $user->username;
            $user->phone = $request['phone'] ?? $user->phone;
            $user->fullname = $request['fullname'] ?? $user->fullname;
            // $user->address = $request['address'] ?? $user->address;
            $user->city_id = $request['city_id'] ?? $user->city_id;
            $user->district_id = $request['district_id'] ?? $user->district_id;
            $user->ward_id = $request['ward_id'] ?? $user->ward_id;
            $user->save();
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return $e->getMessage();
        }
    }
}
