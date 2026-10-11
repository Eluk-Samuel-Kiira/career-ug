<?php

namespace App\Http\Controllers\Seeker;

use App\Http\Controllers\Controller;
use App\Services\CountryService;
use Illuminate\Support\Facades\Session;

class AutoApplyController extends Controller
{
    public function __construct(protected CountryService $countryService) {}

    public function index()
    {
        if (!Session::has('user')) return redirect()->route('login');

        $response = $this->countryService->api('seeker/auto-apply', [], 'GET', 0, false);

        if (!($response['success'] ?? false)) {
            return view('job-seeker.auto-apply.index', [
                'feature_enabled' => false,
                'runs'            => [],
                'stats'           => [],
            ]);
        }

        return view('job-seeker.auto-apply.index', [
            'feature_enabled' => $response['feature_enabled'] ?? false,
            'runs'            => $response['runs'] ?? [],
            'stats'           => $response['stats'] ?? [],
        ]);
    }
}