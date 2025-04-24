<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class AuthenticateApiAdmin extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo(Request $request): ?string
    {
        if (!empty(auth()->user())) {
            if (auth()->user()->role->code !== 'SUPER_ADMIN' || auth()->user()->role->code !== 'ADMIN' || auth()->user()->role->code !== 'DISTRIBUTOR') {
                return $request->expectsJson();
            }
        }
        return null;  // : route('login');
    }
}
