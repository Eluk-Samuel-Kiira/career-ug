<?php

namespace App\Http\Controllers\Jobs;

use App\Http\Controllers\Controller;
use App\Services\CountryService;
use Illuminate\Http\Request;

class JobsController extends Controller
{
    protected CountryService $countryService;

    public function __construct(CountryService $countryService)
    {
        $this->countryService = $countryService;
    }

    /**
     * Display a listing of jobs.
     */
    public function jobs(Request $request)
    {
        $filters = $request->only([
            'search', 'category_id', 'location_id', 'company_id',
            'job_type_id', 'experience_level_id', 'education_level_id',
            'min_salary', 'max_salary', 'page', 'per_page'
        ]);

        $country = $this->countryService->getCountryData();
        $categories = $this->countryService->api('categories', [], 'GET', 3600);
        $locations = $this->countryService->api('locations', [], 'GET', 3600);

        $jobsResponse = $this->countryService->api('jobs', $filters, 'GET', 300, unwrapData: false);
        $featuredResponse = $this->countryService->api('jobs', ['featured' => true, 'per_page' => 10], 'GET', 300, unwrapData: false);
        $featuredJobs = $featuredResponse['data'] ?? [];

        if (empty($jobsResponse) || !isset($jobsResponse['data'])) {
            $jobs = ['data' => [], 'pagination' => ['current_page' => 1, 'last_page' => 1, 'total' => 0]];
        } else {
            $jobs = [
                'data' => $jobsResponse['data'],
                'pagination' => $jobsResponse['pagination'] ?? ['current_page' => 1, 'last_page' => 1, 'total' => count($jobsResponse['data'])],
            ];
        }

        $totalJobs = $jobs['pagination']['total'] ?? 0;

        return view('jobs.index', compact('jobs', 'country', 'categories', 'locations', 'filters', 'featuredJobs', 'totalJobs'));
    }

    /**
     * Display a specific job.
     */
    public function show($id)
    {
        $job = $this->countryService->api('job-action/' . $id);

        if (!$job) {
            abort(404, 'Job not found');
        }

        $country = $this->countryService->getCountryData();

        // Check if job is saved by current user
        $isSaved = false;
        $user = session('user');
        if ($user) {
            $statusResponse = $this->countryService->api("job-action/{$id}/status", [], 'GET', 0, false);
            if (isset($statusResponse['success']) && $statusResponse['success']) {
                $isSaved = $statusResponse['is_saved'] ?? false;
            }
        }

        return view('jobs.show', compact('job', 'country', 'isSaved'));
    }

    /**
     * Save or unsave a job (AJAX)
     */
    public function toggleSave(Request $request, $id)
    {
        $user = session('user');
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Please login to save jobs.',
                'requires_login' => true
            ], 401);
        }

        $result = $this->countryService->api("job-action/{$id}/save", [], 'POST', 0, false);

        if (isset($result['success']) && $result['success']) {
            return response()->json([
                'success' => true,
                'message' => $result['message'] ?? 'Job saved successfully!',
                'is_saved' => $result['is_saved'] ?? true,
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => $result['message'] ?? 'Failed to save job.',
        ], 400);
    }

    /**
     * Unsave a job (AJAX)
     */
    public function unsaveJob(Request $request, $id)
    {
        $user = session('user');
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Please login to unsave jobs.',
                'requires_login' => true
            ], 401);
        }

        $result = $this->countryService->api("job-action/{$id}/save", [], 'POST', 0, false);

        if (isset($result['success']) && $result['success']) {
            return response()->json([
                'success' => true,
                'message' => 'Job removed from saved.',
                'is_saved' => false,
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => $result['message'] ?? 'Failed to unsave job.',
        ], 400);
    }

    /**
     * Get job status (saved, applied, etc.) for current user (AJAX)
     */
    public function getJobStatus(Request $request, $id)
    {
        $user = session('user');
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Please login.',
                'requires_login' => true
            ], 401);
        }

        $result = $this->countryService->api("job-action/{$id}/status", [], 'GET', 0, false);
        return response()->json($result);
    }

    /**
     * Track application for logged-in user (AJAX)
     */
    public function trackApplication(Request $request, $id)
    {
        $user = session('user');
        
        // Always track the application even if not logged in (guest tracking handled separately)
        $result = $this->countryService->api("job-action/{$id}/track-application", [], 'POST', 0, false);

        if (!$user) {
            // For guests, store in session
            $sessionKey = 'guest_applied_jobs';
            $guestJobs = $request->session()->get($sessionKey, []);
            if (!in_array($id, $guestJobs)) {
                $guestJobs[] = $id;
                $request->session()->put($sessionKey, $guestJobs);
            }
            
            return response()->json([
                'success' => true,
                'already_recorded' => false,
                'data' => $result,
                'guest' => true,
            ]);
        }

        $sessionKey = 'applied_job_' . $id;
        if ($request->session()->has($sessionKey)) {
            return response()->json(['success' => true, 'already_recorded' => true]);
        }

        $request->session()->put($sessionKey, true);

        return response()->json([
            'success' => true,
            'already_recorded' => false,
            'data' => $result,
        ]);
    }

    /**
     * Track application for guest users (AJAX)
     */
    public function trackGuestApplication(Request $request)
    {
        $request->validate([
            'job_id' => 'required|integer',
        ]);

        $jobId = $request->job_id;
        $sessionKey = 'guest_applied_jobs';

        $guestJobs = $request->session()->get($sessionKey, []);

        if (!in_array($jobId, $guestJobs)) {
            $guestJobs[] = $jobId;
            $request->session()->put($sessionKey, $guestJobs);
            $this->countryService->api("job-action/{$jobId}/track-application-guest", [], 'POST', 0, false);
        }

        return response()->json([
            'success' => true,
            'message' => 'Application tracked for guest',
        ]);
    }

    /**
     * Sync guest applications when user logs in
     */
    public function syncGuestApplications(Request $request)
    {
        $user = session('user');
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not logged in',
            ], 401);
        }

        $sessionKey = 'guest_applied_jobs';
        $guestJobs = $request->session()->get($sessionKey, []);

        if (empty($guestJobs)) {
            return response()->json([
                'success' => true,
                'message' => 'No guest applications to sync',
            ]);
        }

        $synced = 0;
        foreach ($guestJobs as $jobId) {
            $result = $this->countryService->api("job-action/{$jobId}/track-application", [], 'POST', 0, false);
            if (isset($result['success']) && $result['success']) {
                $synced++;
            }
        }

        $request->session()->forget($sessionKey);

        return response()->json([
            'success' => true,
            'message' => "Synced {$synced} guest applications",
            'synced' => $synced,
        ]);
    }

    /**
     * Display saved jobs page (VIEW)
     */
    public function savedJobs(Request $request)
    {
        $user = session('user');
        if (!$user) {
            return redirect()->route('login')->with('error', 'Please login to view saved jobs.');
        }

        $response = $this->countryService->api('job-action/saved', [], 'GET', 0, false);
        
        $savedJobs = $response['data'] ?? [];
        $total = $response['total'] ?? 0;

        return view('job-seeker.saved-jobs', compact('savedJobs', 'total'));
    }

    /**
     * Display applied jobs page (VIEW)
     */
    public function appliedJobs(Request $request)
    {
        $user = session('user');
        if (!$user) {
            return redirect()->route('login')->with('error', 'Please login to view your applications.');
        }

        $response = $this->countryService->api('job-action/applied', [], 'GET', 0, false);
        
        $appliedJobs = $response['data'] ?? [];
        $total = $response['total'] ?? 0;

        return view('job-seeker.applied-jobs', compact('appliedJobs', 'total'));
    }
}