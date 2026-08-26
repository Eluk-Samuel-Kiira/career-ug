<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateWeb
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Session::get('user');
        $token = Session::get('access_token');

        if (!$user || !$token) {
            return redirect()->route('login')->with('error', 'Please sign in first.');
        }

        // Share user data with all views
        view()->share('auth_user', $user);
        view()->share('auth_token', $token);
        view()->share('auth_role', $user['role'] ?? 'job_seeker');

        return $next($request);
    }
}