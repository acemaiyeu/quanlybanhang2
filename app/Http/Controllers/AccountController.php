<?php

namespace App\Http\Controllers;

use App\Http\Requests\ChangePasswordAccountValidator;
use App\Http\Requests\CreateAccountValidator;
use App\Http\Requests\DeleteAccountValidator;
use App\Http\Requests\DetailAccountValidator;
use App\Http\Requests\UpdateAccountValidator;
use App\ModelQuery\AccountModel;
use App\Models\User;
use App\Transformers\AccountTransformer;
use Illuminate\Http\Request;

class AccountController extends Controller
{
    protected $model;

    public function __construct(AccountModel $model)
    {
        $this->model = $model;
    }

    public function getAllAccounts(Request $request)
    {
        $user = $this->model->getAccounts($request);
        return fractal($user, new AccountTransformer())->respond();
    }

    public function createAccount(CreateAccountValidator $request)
    {
        $user = $this->model->createAccount($request);

        return fractal($user, new AccountTransformer())->respond();
    }

    public function updateAccount(UpdateAccountValidator $request)
    {
        $user = $this->model->updateAccount($request);
        return fractal($user, new AccountTransformer())->respond();
    }

    public function deleteAccount($id)
    {
        $user = User::whereNull('deleted_at')->where('id', $id)->first();
        if (!$user) {
            return response()->json(['errors' => ['status' => '422', 'id' => 'Tài khoản không tồn tại'], 'message' => 'Validation Failed'], 422);
        }
        $user = $this->model->deleteAccount($user);
        return response()->json(['status' => '200', 'message' => 'success'], 200);
    }

    public function getDetailAccount(DetailAccountValidator $request, $id)
    {
        $request['limit'] = 1;
        $request['id'] = $id;
        $user = $this->model->getAccounts($request);
        return fractal($user, new AccountTransformer())->respond();
    }

    public function changePasswordAccount(ChangePasswordAccountValidator $request)
    {
        $user = $this->model->changePasswordAccount($request);
        return response()->json(['status' => '200', 'message' => 'success'], 200);
    }
}
