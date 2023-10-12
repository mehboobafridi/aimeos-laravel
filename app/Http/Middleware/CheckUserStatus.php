<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class CheckUserStatus
{
    public function handle($request, Closure $next)
    {
        if (Auth::check()) {
            $user = Auth::user();
            if ($user->user_type == 0) {
                Auth::logout();
                return redirect()->route('login')->with('status', 'Your account is inactive.');
            }
            $app_id = intval(env('APP_ID'));

            if ($user->user_type != $app_id && $user->user_type != 3) {
                Auth::logout();
                return redirect()->route('login')->with('status', 'You are not allowed.');
            }
        }

        return $next($request);
    }
}
