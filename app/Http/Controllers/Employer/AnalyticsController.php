<?php

namespace App\Http\Controllers\Employer;

use App\Http\Controllers\Controller;
use App\Services\CountryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class AnalyticsController extends Controller
{
    public function __construct(protected CountryService $countryService) {}

    /**
     * The Analytics page — loads the shell, then JS fetches the data.
     */
    public function index()
    {
        if (!Session::has('user')) {
            return redirect()->route('login');
        }

        // Fetch jobs for the filter dropdown
        $filters = $this->countryService->api('employer/analytics/filters', [], 'GET', 0, false);
        $jobs    = $filters['jobs'] ?? [];

        return view('employer.analytics.index', compact('jobs'));
    }

    /**
     * Proxy the /data endpoint. Browser JS calls this.
     */
    public function data(Request $request)
    {
        if (!Session::has('user')) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }

        $params = $request->only(['range', 'job_id']);

        $response = $this->countryService->api('employer/analytics/data', $params, 'GET', 0, false);

        return response()->json($response);
    }
}