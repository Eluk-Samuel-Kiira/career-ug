<?php

namespace App\Http\Controllers\Seeker;

use App\Http\Controllers\Controller;
use App\Services\CountryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class AutoApplyController extends Controller
{
    public function __construct(protected CountryService $countryService) {}

    public function index(Request $request)
    {
        if (!Session::has('user')) {
            return redirect()->route('login');
        }

        $params = array_filter([
            'page'     => $request->get('page'),
            'per_page' => $request->get('per_page'),
        ], fn ($v) => $v !== null && $v !== '');

        $response = $this->countryService->api('seeker/auto-apply', $params, 'GET', 0, false);

        if (!($response['success'] ?? false)) {
            return view('job-seeker.auto-apply.index', [
                'feature_enabled' => false,
                'runs'            => [],
                'stats'           => [],
                'pagination'      => [
                    'current_page' => 1, 'last_page' => 1,
                    'per_page' => 10, 'total' => 0,
                ],
            ]);
        }

        return view('job-seeker.auto-apply.index', [
            'feature_enabled' => $response['feature_enabled'] ?? false,
            'runs'            => $response['runs'] ?? [],
            'stats'           => $response['stats'] ?? [],
            'pagination'      => $response['pagination'] ?? [
                'current_page' => 1, 'last_page' => 1,
                'per_page' => 10, 'total' => count($response['runs'] ?? []),
            ],
        ]);
    }

}