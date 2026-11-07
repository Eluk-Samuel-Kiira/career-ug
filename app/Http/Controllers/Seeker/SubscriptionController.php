<?php

namespace App\Http\Controllers\Seeker;

use App\Http\Controllers\Controller;
use App\Services\CountryService;
use Illuminate\Support\Facades\Session;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    public function __construct(protected CountryService $countryService) {}

    public function index(Request $request)
    {
        if (!Session::has('user')) {
            return redirect()->route('login')->with('error', 'Please sign in first.');
        }

        $params = array_filter([
            'payments_page' => $request->get('payments_page'),
            'subs_page'     => $request->get('subs_page'),
        ], fn ($v) => $v !== null && $v !== '');

        $response = $this->countryService->api('user/subscriptions', $params, 'GET', 0, false);

        if (!($response['success'] ?? false)) {
            return redirect()->route('dashboard')->with('error', 'Could not load your subscription data.');
        }

        return view('seeker.subscription.index', [
            'active'        => $response['active'] ?? null,
            'subscriptions' => $response['subscriptions'] ?? [],
            'payments'      => $response['payments'] ?? [],
            'summary'       => $response['summary'] ?? [],
            'pagination'    => $response['pagination'] ?? [],
            'pricingUrl'    => route('pricing.index'),
        ]);
    }

}