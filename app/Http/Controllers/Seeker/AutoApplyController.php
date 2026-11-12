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

        // ✅ Define featureEnabled from the API response (defaults to false)
        $featureEnabled = $response['feature_enabled'] ?? false;

        // If the API call failed entirely, short-circuit with the locked state
        if (!($response['success'] ?? false)) {
            $subscriptionPrice = null;
            $priceResponse = $this->countryService->api('services/pricing/auto-apply', [], 'GET', 0, false);
            if ($priceResponse['success'] ?? false) {
                $subscriptionPrice = $priceResponse['price'];
            }

            return view('job-seeker.auto-apply.index', [
                'feature_enabled'   => false,
                'runs'              => [],
                'applied_jobs'      => [],
                'stats'             => [],
                'subscriptionPrice' => $subscriptionPrice,
                'pagination'        => [
                    'current_page' => 1, 'last_page' => 1,
                    'per_page' => 10, 'total' => 0,
                ],
            ]);
        }

        // Fetch the subscription price only if the user does not have an active plan
        $subscriptionPrice = null;
        if (!$featureEnabled) {
            $priceResponse = $this->countryService->api('services/pricing/auto-apply', [], 'GET', 0, false);
            if ($priceResponse['success'] ?? false) {
                $subscriptionPrice = $priceResponse['price'];
            }
        }

        return view('job-seeker.auto-apply.index', [
            'feature_enabled'   => $featureEnabled,
            'runs'              => $response['runs'] ?? [],
            'applied_jobs'      => $response['applied_jobs'] ?? [],
            'stats'             => $response['stats'] ?? [],
            'subscriptionPrice' => $subscriptionPrice,
            'pagination'        => $response['pagination'] ?? [
                'current_page' => 1, 'last_page' => 1,
                'per_page' => 10, 'total' => count($response['runs'] ?? []),
            ],
        ]);
    }

    public function checkStatus(Request $request)
    {
        $reference = $request->input('reference');
        
        $response = $this->countryService->api("payments/status/{$reference}", [], 'GET', 0, false);
        
        return response()->json($response);
    }

    public function subscribe(Request $request)
    {
        if (!Session::has('user')) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }

        $user = Session::get('user');

        // 1. Fetch the price from the Main API (just to get the amount and currency)
        $priceResponse = $this->countryService->api('services/pricing/auto-apply', [], 'GET', 0, false);
        
        if (!($priceResponse['success'] ?? false)) {
            return response()->json(['success' => false, 'message' => 'Pricing not found.'], 422);
        }

        $price = $priceResponse['price'];

        // 2. Generate a temporary UUID for the subscription reference
        // The webhook will find this UUID and create the subscription for this user
        $referenceUuid = (string) \Illuminate\Support\Str::uuid();

        // 3. Call the Main App Init API
        $response = $this->countryService->api(
            'payments/initiate',
            [
                'amount_cents'      => $price['amount_cents'],
                'currency_code'     => $price['currency'],
                'purpose_type'      => 'auto_apply_subscription',
                'purpose_reference' => $referenceUuid,
                'description'       => 'Auto Apply Agent Subscription',
                'phone'             => $request->input('phone'),
                'method'            => $request->input('method', 'mobile_money'),
            ],
            'POST',
            0,
            false
        );

        return response()->json($response);
    }

}