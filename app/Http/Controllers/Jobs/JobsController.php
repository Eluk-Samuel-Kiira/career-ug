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

        // unwrapData: false - the jobs endpoint returns {success, data, pagination}.
        // Unwrapping it to just 'data' (the old default) silently threw pagination
        // away, which made the `!isset($jobs['data'])` guard below fire on every
        // request (since a plain numeric jobs array never has a 'data' key) and
        // reset real results back to empty every time.
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
        $job = $this->countryService->api('jobs/' . $id);

        if (!$job) {
            abort(404, 'Job not found');
        }

        $country = $this->countryService->getCountryData();

        return view('jobs.show', compact('job', 'country'));
    }

    /**
     * Record that the apply modal was opened for a job. Called from JS the
     * moment #applyModal fires 'show.bs.modal' - see jobs/show.blade.php.
     *
     * This is the browser's same-origin target (it proxies to the main app
     * via CountryService, which is where the Authorization header and API
     * key live) - the browser never talks to the main app directly.
     *
     * Session-based de-dupe: reopening the modal, or refreshing the page and
     * clicking Apply again, does not inflate application_count repeatedly
     * within the same session - only the first open per job per session counts.
     */
    public function trackApplication(Request $request, $id)
    {
        // \Log::info($id);
        $sessionKey = 'applied_job_' . $id;
 
        if ($request->session()->has($sessionKey)) {
            return response()->json(['success' => true, 'already_recorded' => true]);
        }
 
        $result = $this->countryService->api("jobs/{$id}/track-application", [], 'POST');
 
        $request->session()->put($sessionKey, true);
 
        return response()->json([
            'success' => true,
            'already_recorded' => false,
            'data' => $result,
        ]);
    }

}