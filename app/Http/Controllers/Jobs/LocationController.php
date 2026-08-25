<?php

namespace App\Http\Controllers\Jobs;

use App\Http\Controllers\Controller;
use App\Services\CountryService;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    protected CountryService $countryService;

    public function __construct(CountryService $countryService)
    {
        $this->countryService = $countryService;
    }

    /**
     * Display jobs for a specific location
     */
    public function show(Request $request, $slug)
    {
        // Get country data
        $country = $this->countryService->getCountryData();

        // Get all locations to find the location by slug
        $allLocations = $this->countryService->api('locations', [], 'GET', 3600);
        
        // Find location by slug (case-insensitive)
        $location = collect($allLocations)->firstWhere('slug', $slug);

        // If not found by slug, try to find by district name
        if (!$location) {
            $location = collect($allLocations)->firstWhere('district', ucfirst(str_replace('-', ' ', $slug)));
        }

        // If still not found, try by ID (for backward compatibility)
        if (!$location && is_numeric($slug)) {
            $location = collect($allLocations)->firstWhere('id', (int)$slug);
        }

        if (!$location) {
            abort(404, 'Location not found');
        }

        $locationId = $location['id'];

        // Build filters for jobs
        $filters = [
            'location_id' => $locationId,
            'per_page' => $request->input('per_page', 20),
            'page' => $request->input('page', 1),
        ];

        // Add search if present
        if ($request->has('search') && !empty($request->search)) {
            $filters['search'] = $request->search;
        }

        // Add category filter if present
        if ($request->has('category_id') && !empty($request->category_id)) {
            $filters['category_id'] = $request->category_id;
        }

        // Add job type filter if present
        if ($request->has('job_type_id') && !empty($request->job_type_id)) {
            $filters['job_type_id'] = $request->job_type_id;
        }

        // Add salary filters if present
        if ($request->has('min_salary') && !empty($request->min_salary)) {
            $filters['min_salary'] = $request->min_salary;
        }
        if ($request->has('max_salary') && !empty($request->max_salary)) {
            $filters['max_salary'] = $request->max_salary;
        }

        // Add sort if present
        if ($request->has('sort') && !empty($request->sort)) {
            $filters['sort'] = $request->sort;
        }

        // Log the request
        // \Log::info('LocationController@show - Fetching jobs for location', [
        //     'slug' => $slug,
        //     'location_id' => $locationId,
        //     'location_name' => $location['display_name'] ?? 'Unknown',
        //     'filters' => $filters
        // ]);

        // Fetch jobs for this location
        $jobsResponse = $this->countryService->api('jobs', $filters, 'GET', 300, false);

        if (empty($jobsResponse) || !isset($jobsResponse['data'])) {
            $jobs = ['data' => [], 'pagination' => ['current_page' => 1, 'last_page' => 1, 'total' => 0]];
        } else {
            $jobs = [
                'data' => $jobsResponse['data'],
                'pagination' => $jobsResponse['pagination'] ?? ['current_page' => 1, 'last_page' => 1, 'total' => 0],
            ];
        }

        // Fetch categories, job types, and locations for filters
        $categories = $this->countryService->api('categories', [], 'GET', 3600);
        $jobTypes = $this->countryService->api('job-types', [], 'GET', 3600);
        $locations = $this->countryService->api('locations', [], 'GET', 3600);

        return view('jobs.location', compact('country', 'location', 'jobs', 'categories', 'jobTypes', 'locations'));
    }
}