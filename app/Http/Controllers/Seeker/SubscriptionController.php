<?php

namespace App\Http\Controllers\Seeker;

use App\Http\Controllers\Controller;
use App\Services\CountryService;
use Illuminate\Support\Facades\Session;

class SubscriptionController extends Controller
{
    public function __construct(protected CountryService $countryService) {}

    public function index()
    {
        if (!Session::has('user')) {
            return redirect()->route('login');
        }

        $response = $this->countryService->api('user/subscriptions', [], 'GET', 0, false);

        return view('seeker.subscription.index', [
            'active'        => $response['active'] ?? null,
            'subscriptions' => $response['subscriptions'] ?? [],
            'payments'      => $response['payments'] ?? [],
            'summary'       => $response['summary'] ?? [],
            'pricingUrl'    => route('pricing.index'),
        ]);
    }
}