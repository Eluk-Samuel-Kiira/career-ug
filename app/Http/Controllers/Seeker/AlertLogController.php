<?php

namespace App\Http\Controllers\Seeker;

use App\Http\Controllers\Controller;
use App\Services\CountryService;
use Illuminate\Support\Facades\Session;

class AlertLogController extends Controller
{
    public function __construct(protected CountryService $countryService) {}

    public function index()
    {
        if (!Session::has('user')) {
            return redirect()->route('login');
        }

        $response = $this->countryService->api('seeker/alert-log', [], 'GET', 0, false);

        return view('seeker.alert-log.index', [
            'featureActive'    => $response['feature_active'] ?? false,
            'hasSubscription'  => $response['has_any_subscription'] ?? false,
            'pricingUrl'       => route('pricing.index'),
        ]);
    }

    public function data(\Illuminate\Http\Request $request)
    {
        if (!Session::has('user')) {
            return response()->json(['success' => false], 401);
        }

        return response()->json(
            $this->countryService->api(
                'seeker/alert-log',
                $request->only(['status', 'kind', 'from', 'to', 'page', 'per_page']),
                'GET',
                0,
                false
            )
        );
    }
}