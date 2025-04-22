<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginValidator;
use App\Http\Requests\RegisterValidator;
use App\ModelQuery\UserModel;
use App\Models\Role;
use App\Models\User;
use App\Transformers\ProfileTransformer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    protected $userModel;

    public function __construct(UserModel $model)
    {
        $this->userModel = $model;
    }

    public function login(LoginValidator $request)
    {
        $credentials = $request->only('email', 'password');

        if (!$token = JWTAuth::attempt($credentials)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60
        ]);
    }

    public function register(RegisterValidator $request)
    {
        $role = Role::where('code', 'GUEST')->first();
        if ($role) {
            $user = User::create([
                'username' => $request->username ?? '',
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role_id' => $role->id
            ]);

            $token = JWTAuth::fromUser($user);
            return response()->json([
                'access_token' => $token,
                'token_type' => 'bearer',
                'expires_in' => auth()->factory()->getTTL() * 60
            ]);
        } else {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }

    public function profile()
    {
        if (!empty(auth()->user())) {
            // return response()->json(auth()->user());
            $user = auth()->user();
            return fractal($user, new ProfileTransformer())->respond();
        } else {
            return response()->json(['errors' => [
                'message' => 'Bạn chưa đăng nhập hoặc token không hợp lệ2.'
            ]], 401);
        }
    }

    public function logout()
    {
        auth()->logout();
        return response()->json(['message' => 'Successfully logged out']);
    }

    public function refresh()
    {
        return response()->json([
            'access_token' => auth()->refresh(),
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60
        ]);
    }

    public function updateProfile(Request $request)
    {
        try {
            $user = $this->userModel->updateUser($request);

            return fractal(auth()->user(), new ProfileTransformer())->respond();
        } catch (\Exception $e) {
            // Trả về lỗi với status 400 hoặc 500 tùy ý
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }
}
