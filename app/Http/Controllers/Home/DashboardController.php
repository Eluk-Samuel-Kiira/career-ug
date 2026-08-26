<?php

namespace App\Http\Controllers\Home;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Http;

class DashboardController extends Controller
{
    /**
     * Main dashboard - shows content based on user role
     */
    public function index(Request $request)
    {
        $user = Session::get('user');
        $token = Session::get('access_token');
        $role = $user['role'] ?? 'job_seeker';

        // Fetch dashboard data from main app API
        $dashboardData = $this->getDashboardData($token);

        return view('dashboard.index', compact('user', 'role', 'dashboardData'));
    }

    /**
     * Employer specific dashboard (redirects to main with employer view)
     */
    public function employerDashboard(Request $request)
    {
        $user = Session::get('user');
        $token = Session::get('access_token');
        $role = $user['role'] ?? 'employer';

        return redirect()->route('dashboard')->with('role', 'employer');
    }

    /**
     * Seeker specific dashboard (redirects to main with seeker view)
     */
    public function seekerDashboard(Request $request)
    {
        $user = Session::get('user');
        $token = Session::get('access_token');
        $role = $user['role'] ?? 'job_seeker';

        return redirect()->route('dashboard')->with('role', 'seeker');
    }

    /**
     * Fetch dashboard data from main app API
     */
    private function getDashboardData($token)
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'Accept' => 'application/json',
            ])->get(config('app.main_app_url') . '/api/dashboard/stats');

            if ($response->successful()) {
                return $response->json()['data'] ?? [];
            }
        } catch (\Exception $e) {
            // Log error but don't fail
        }

        return [];
    }
}