<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\CountryService;

class UnsubscribeController extends Controller
{
    protected CountryService $countryService;

    public function __construct(CountryService $countryService)
    {
        $this->countryService = $countryService;
    }

    public function handle(Request $request)
    {
        $userId    = $request->query('user');
        $signature = $request->query('signature');
        $expires   = $request->query('expires');

        if (!$userId || !$signature || !$expires) {
            return view('unsubscribe', [
                'status'  => 'error',
                'message' => 'Invalid unsubscribe link. Please use the link provided in your most recent email.'
            ]);
        }

        // 1. Get the ORIGINAL query string from the incoming request
        //    This preserves the exact order Laravel signed it with.
        $originalQueryString = $request->getQueryString();

        // 2. Build the exact URL the MAIN APP signed
        $mainAppUrl = rtrim(config('app.main_app_url', 'http://127.0.0.1:8000'), '/');
        
        // 3. The signature was generated for /api/unsubscribe
        $absoluteMainAppUrl = "{$mainAppUrl}/api/unsubscribe?{$originalQueryString}";

        // 4. Send it to the main app for verification
        $response = $this->countryService->sendSignedRequest($absoluteMainAppUrl);

        // 5. Handle response
        if (isset($response['success']) && $response['success'] === true) {
            return view('unsubscribe', [
                'status'  => 'success',
                'message' => $response['message'] ?? 'Successfully unsubscribed.'
            ]);
        }

        return view('unsubscribe', [
            'status'  => 'error',
            'message' => $response['message'] ?? 'Invalid or expired unsubscribe link.'
        ]);
    }
}