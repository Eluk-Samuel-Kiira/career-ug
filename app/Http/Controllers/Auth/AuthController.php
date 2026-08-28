<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\CountryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{ Log, Session };

class AuthController extends Controller
{
    protected CountryService $countryService;

    public function __construct(CountryService $countryService)
    {
        $this->countryService = $countryService;
    }

    /**
     * Register user - sends to main app via API
     */
    public function register(Request $request)
    {
        // Get account type
        $accountType = $request->account_type ?? 'seeker';

        // Build validation rules
        $rules = [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email',
            'phone' => 'nullable|string|max:20',
            'account_type' => 'nullable|in:seeker,employer',
            'desired_title' => 'nullable|string|max:255',
            'country_code' => 'nullable|string|size:2',
            'terms' => 'accepted',
        ];

        // Add conditional validation for employer
        if ($accountType === 'employer') {
            $rules['company_name'] = 'required|string|max:255';
            $rules['contact_name'] = 'required|string|max:255';
            $rules['company_size'] = 'nullable|string|max:50';
        }

        $request->validate($rules);

        // Build phone number with country code
        $phone = $request->phone;
        if ($phone) {
            $countryCode = $request->country_code ?? config('app.country_code', 'UG');
            // Remove any existing country code from the phone
            $phone = ltrim($phone, '+');
            // Add country code prefix if not present
            if (!str_starts_with($phone, config('app.country_phone_code', '256'))) {
                $phone = config('app.country_phone_code', '256') . $phone;
            }
        }

        // Determine the role based on account_type
        $role = $accountType === 'employer' ? 'employer' : 'job_seeker';

        // Build the data array to send to main app
        $data = [
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'phone' => $phone,
            'role' => $role,
            'desired_title' => $request->desired_title,
            'company_name' => $request->company_name,
            'company_size' => $request->company_size,
            'country_code' => $request->country_code ?? config('app.country_code', 'UG'),
            'terms' => true,
        ];

        // Send to main app via CountryService
        $response = $this->countryService->api('auth/register', $data, 'POST', 0, false);

        // Check if registration was successful
        if (isset($response['success']) && $response['success']) {
            return response()->json([
                'success' => true,
                'message' => $response['message'] ?? 'Account created! Check your email for the magic link.'
            ]);
        }

        // Return error response
        return response()->json([
            'success' => false,
            'message' => $response['message'] ?? 'Registration failed. Please try again.',
            'errors' => $response['errors'] ?? null
        ], 400);
    }

    /**
     * Send magic link for login
     */
    public function sendMagicLink(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $response = $this->countryService->api('auth/send-login-link', [
            'email' => $request->email,
        ], 'POST', 0, false);

        if (isset($response['success']) && $response['success']) {
            return response()->json([
                'success' => true,
                'message' => $response['message'] ?? 'Magic link sent to your email!'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => $response['message'] ?? 'Failed to send magic link.'
        ], 400);
    }

    /**
     * Verify magic link and create session
     */
    public function verifyMagicLink($token)
    {
        // Log::info('Verifying magic link', ['token' => substr($token, 0, 20) . '...']);

        $response = $this->countryService->api('auth/verify-token', [
            'token' => $token,
        ], 'POST', 0, false);

        // Log::info('Verify token response', ['response' => $response]);

        if (isset($response['success']) && $response['success'] && isset($response['user'])) {
            // Store user data in session
            Session::put('user', $response['user']);
            Session::put('access_token', $response['api_token']);
            Session::put('account_type', $response['user']['role'] ?? 'job_seeker');

            // Log::info('User authenticated successfully', [
            //     'user_id' => $response['user']['id'],
            //     'email' => $response['user']['email'],
            //     'role' => $response['user']['role'] ?? 'job_seeker'
            // ]);

            // Both roles go to the same dashboard - role determines content
            return redirect()->route('dashboard');
        }

        Log::warning('Magic link verification failed', [
            'token' => substr($token, 0, 20) . '...',
            'response' => $response
        ]);

        return redirect()->route('login')
            ->with('error', $response['message'] ?? 'Invalid or expired magic link.');
    }


    /**
     * Logout
     */
    public function logout(Request $request)
    {
        $accessToken = Session::get('access_token');

        if ($accessToken) {
            $this->countryService->api('auth/logout', [], 'POST', 0, false);
        }

        Session::flush();
        return redirect()->route('login')->with('success', 'You have been logged out.');
    }

    /**
     * Get current user (for API)
     */
    public function getUser()
    {
        $accessToken = Session::get('access_token');

        if (!$accessToken) {
            return response()->json(['authenticated' => false], 401);
        }

        $response = $this->countryService->api('auth/user', [], 'GET', 0, false);

        if (isset($response['success']) && $response['success']) {
            return response()->json($response);
        }

        return response()->json(['authenticated' => false], 401);
    }
}