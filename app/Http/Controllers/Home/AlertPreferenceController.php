<?php

namespace App\Http\Controllers\Home;

use App\Http\Controllers\Controller;
use App\Services\CountryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;

class AlertPreferenceController extends Controller
{
    protected CountryService $countryService;

    public function __construct(CountryService $countryService)
    {
        $this->countryService = $countryService;
    }

    public function edit()
    {
        $user = Session::get('user');
        if (!$user) {
            return redirect()->route('login')->with('error', 'Please sign in first.');
        }

        // Get user profile with preferences
        $response = $this->countryService->api('auth/user', [], 'GET', 0, false);
        $profile = $response['user'] ?? $user;
        
        // Get categories for the dropdown
        $categories = $this->countryService->api('all_categories', [], 'GET', 3600);
        $categories = is_array($categories) ? $categories : [];
        
        // Get job preferences from profile
        $jobPreferences = $profile['job_preferences'] ?? [];
        
        return view('job-seeker.alert-preferences', compact('profile', 'categories', 'jobPreferences'));
    }

    public function update(Request $request)
    {
        $user = Session::get('user');
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }

        $request->validate([
            'all_job_alerts' => 'nullable|boolean',
            'alert_by_cv_skill' => 'nullable|boolean',
            'alert_by_skill_match' => 'nullable|boolean',
            'skill_match_threshold' => 'nullable|integer|min:0|max:100',
            'allow_employer_view_my_cv' => 'nullable|boolean',
            'send_alert_by_whatsapp' => 'nullable|boolean',
            'send_alert_by_telegram' => 'nullable|boolean',
            'send_alert_by_email' => 'nullable|boolean',
            'alert_by_category' => 'nullable|array',
            'alert_by_category.*' => 'integer',
        ]);

        $jobPreferences = [
            'all_job_alerts' => $request->has('all_job_alerts') ? filter_var($request->all_job_alerts, FILTER_VALIDATE_BOOLEAN) : false,
            'alert_by_cv_skill' => $request->has('alert_by_cv_skill') ? filter_var($request->alert_by_cv_skill, FILTER_VALIDATE_BOOLEAN) : false,
            'alert_by_skill_match' => $request->has('alert_by_skill_match') ? filter_var($request->alert_by_skill_match, FILTER_VALIDATE_BOOLEAN) : false,
            'skill_match_threshold' => $request->skill_match_threshold ?? 60,
            'allow_employer_view_my_cv' => $request->has('allow_employer_view_my_cv') ? filter_var($request->allow_employer_view_my_cv, FILTER_VALIDATE_BOOLEAN) : false,
            'send_alert_by_whatsapp' => $request->has('send_alert_by_whatsapp') ? filter_var($request->send_alert_by_whatsapp, FILTER_VALIDATE_BOOLEAN) : false,
            'send_alert_by_telegram' => $request->has('send_alert_by_telegram') ? filter_var($request->send_alert_by_telegram, FILTER_VALIDATE_BOOLEAN) : false,
            'send_alert_by_email' => $request->has('send_alert_by_email') ? filter_var($request->send_alert_by_email, FILTER_VALIDATE_BOOLEAN) : false,
            'alert_by_category' => $request->alert_by_category ?? [],
        ];

        $data = [
            'job_preferences' => $jobPreferences,
        ];

        $response = $this->countryService->api('auth/user/update', $data, 'PUT', 0, false);

        if (isset($response['success']) && $response['success']) {
            if (isset($response['user'])) {
                Session::put('user', $response['user']);
            }
            return response()->json([
                'success' => true,
                'message' => 'Alert preferences updated successfully!',
                'job_preferences' => $jobPreferences,
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => $response['message'] ?? 'Failed to update preferences.',
        ], 400);
    }
}