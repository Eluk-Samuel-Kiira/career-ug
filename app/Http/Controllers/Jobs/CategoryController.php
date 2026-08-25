<?php

namespace App\Http\Controllers\Jobs;

use App\Http\Controllers\Controller;
use App\Services\CountryService;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    protected CountryService $countryService;

    public function __construct(CountryService $countryService)
    {
        $this->countryService = $countryService;
    }

    /**
     * Display jobs for a specific category by slug
     */
    public function show(Request $request, $slug)
    {
        // Get country data
        $country = $this->countryService->getCountryData();

        // Get all categories to find the category by slug
        $allCategories = $this->countryService->api('categories', [], 'GET', 3600);
        
        // Find category by slug (case-insensitive)
        $category = collect($allCategories)->firstWhere('slug', $slug);

        // If not found by slug, try to find by ID (for backward compatibility)
        if (!$category && is_numeric($slug)) {
            $category = collect($allCategories)->firstWhere('id', (int)$slug);
        }

        if (!$category) {
            abort(404, 'Category not found');
        }

        $categoryId = $category['id'];

        // Build filters for jobs
        $filters = [
            'category_id' => $categoryId,
            'per_page' => $request->input('per_page', 20),
            'page' => $request->input('page', 1),
        ];

        // Add search if present
        if ($request->has('search') && !empty($request->search)) {
            $filters['search'] = $request->search;
        }

        // Add job type filter if present
        if ($request->has('job_type_id') && !empty($request->job_type_id)) {
            $filters['job_type_id'] = $request->job_type_id;
        }

        // Add location filter if present
        if ($request->has('location_id') && !empty($request->location_id)) {
            $filters['location_id'] = $request->location_id;
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
        // \Log::info('CategoryController@show - Fetching jobs for category', [
        //     'slug' => $slug,
        //     'category_id' => $categoryId,
        //     'category_name' => $category['name'] ?? 'Unknown',
        //     'filters' => $filters
        // ]);

        // Fetch jobs for this category
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
        // \Log::info($locations);

        return view('jobs.category', compact('country', 'category', 'jobs', 'categories', 'jobTypes', 'locations'));
    }
}