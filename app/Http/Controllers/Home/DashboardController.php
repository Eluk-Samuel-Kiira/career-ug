<?php

namespace App\Http\Controllers\Home;

use App\Http\Controllers\Controller;
use App\Services\CountryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class DashboardController extends Controller
{
    public function __construct(protected CountryService $countryService) {}

    public function index(Request $request)
    {
        $user = Session::get('user');
        if (!$user) {
            return redirect()->route('login');
        }

        $role = $user['role'] ?? 'job_seeker';
        $isEmployer = $role === 'employer';

        // Single API call — hit the right endpoint based on role
        $endpoint = $isEmployer ? 'dashboard/employer' : 'dashboard/seeker';
        $response = $this->countryService->api($endpoint, [], 'GET', 0, false);

        $needsOnboarding = $response['needsOnboarding'] ?? true;
        $stats           = $response['stats'] ?? null;
        $recentActivity  = $response['recentActivity'] ?? [];

        return view('dashboard.index', compact(
            'user',
            'role',
            'needsOnboarding',
            'stats',
            'recentActivity'
        ));
    }
}