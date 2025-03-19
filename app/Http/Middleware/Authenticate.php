<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo(Request $request)
    {
        return null;
    }

    protected function unauthenticated($request, array $guards){
        abort(response()->json([
            'message' => 'You are not authorized, please log in!',], 401));
    }
}
