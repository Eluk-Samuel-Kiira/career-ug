<?php

namespace App\Http\Middleware;

use App\Services\CountryService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Session, Cache, Log};
use Symfony\Component\HttpFoundation\Response;

class AuthenticateWeb
{
    public function __construct(protected CountryService $countryService) {}

    public function handle(Request $request, Closure $next): Response
    {
        $user  = Session::get('user');
        $token = Session::get('access_token');

        // ── 1. No session at all ─────────────────────────────────
        if (!$user || !$token) {
            return $this->logoutAndRedirect($request, 'Please sign in first.');
        }

        // ── 2. Validate the token against the main app ──────────
        //    Cache the result for 60s so we don't hammer the API
        $cacheKey = 'auth_check:' . md5($token);

        $isValid = Cache::remember($cacheKey, 60, function () use ($token) {
            try {
                $response = $this->countryService->api('auth/user', [], 'GET', 0, false);

                // API returns 401 when token is dead
                if (!is_array($response) || !($response['success'] ?? false)) {
                    return false;
                }

                // Verify the returned user matches the session user
                $returnedUser = $response['user'] ?? null;
                if (!$returnedUser || ($returnedUser['id'] ?? null) !== (Session::get('user.id'))) {
                    return false;
                }

                return true;
            } catch (\Throwable $e) {
                Log::warning('Auth check failed', ['error' => $e->getMessage()]);
                return false;
            }
        });

        if (!$isValid) {
            Cache::forget($cacheKey);
            return $this->logoutAndRedirect($request, 'Your session has expired. Please sign in again.');
        }

        // ── 3. Share with views ─────────────────────────────────
        view()->share('auth_user', $user);
        view()->share('auth_token', $token);
        view()->share('auth_role', $user['role'] ?? 'job_seeker');

        return $next($request);
    }

    protected function logoutAndRedirect(Request $request, string $message): Response
    {
        // Best-effort: tell the main app to revoke the token
        $token = Session::get('access_token');
        if ($token) {
            try {
                $this->countryService->api('auth/logout', [], 'POST', 0, false);
            } catch (\Throwable $e) {
                // swallow — we're logging out anyway
            }
            Cache::forget('auth_check:' . md5($token));
        }

        Session::flush();

        // AJAX / JSON clients get a proper 401
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => false,
                'message' => $message,
                'code'    => 'unauthenticated',
            ], 401);
        }

        return redirect()->route('login')->with('error', $message);
    }
}