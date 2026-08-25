<?php

namespace App\Http\Controllers\Jobs;

use App\Http\Controllers\Controller;
use App\Services\CountryService;
use Illuminate\Http\Request;

class CompaniesController extends Controller
{
    protected CountryService $countryService;

    public function __construct(CountryService $countryService)
    {
        $this->countryService = $countryService;
    }

    /**
     * Display a listing of companies.
     */
    public function companies(Request $request)
    {
        $filters = $request->only([
            'search', 'industry_id', 'location_id', 
            'is_verified', 'is_featured', 'is_gold',
            'page', 'per_page', 'sort'
        ]);

        $country = $this->countryService->getCountryData();
        $industries = $this->countryService->api('industries', [], 'GET', 3600);
        $locations = $this->countryService->api('locations', [], 'GET', 3600);

        // Fetch companies from API
        $companiesResponse = $this->countryService->api('companies', $filters, 'GET', 300, unwrapData: false);

        if (empty($companiesResponse) || !isset($companiesResponse['data'])) {
            $companies = ['data' => [], 'pagination' => ['current_page' => 1, 'last_page' => 1, 'total' => 0]];
        } else {
            $companies = [
                'data' => $companiesResponse['data'],
                'pagination' => $companiesResponse['pagination'] ?? ['current_page' => 1, 'last_page' => 1, 'total' => count($companiesResponse['data'])],
            ];
        }

        $totalCompanies = $companies['pagination']['total'] ?? 0;

        return view('companies.index', compact('companies', 'country', 'industries', 'locations', 'filters', 'totalCompanies'));
    }

    /**
     * Display a specific company with its jobs.
     */
    public function show(Request $request, $id)
    {
        // Build query parameters from request
        $queryParams = [];
        
        if ($request->has('search') && !empty($request->search)) {
            $queryParams['search'] = $request->search;
        }
        if ($request->has('category_id') && !empty($request->category_id)) {
            $queryParams['category_id'] = $request->category_id;
        }
        if ($request->has('job_type_id') && !empty($request->job_type_id)) {
            $queryParams['job_type_id'] = $request->job_type_id;
        }
        if ($request->has('location_id') && !empty($request->location_id)) {
            $queryParams['location_id'] = $request->location_id;
        }
        if ($request->has('min_salary') && !empty($request->min_salary)) {
            $queryParams['min_salary'] = $request->min_salary;
        }
        if ($request->has('max_salary') && !empty($request->max_salary)) {
            $queryParams['max_salary'] = $request->max_salary;
        }
        if ($request->has('sort') && !empty($request->sort)) {
            $queryParams['sort'] = $request->sort;
        }
        if ($request->has('page') && !empty($request->page)) {
            $queryParams['page'] = $request->page;
        }
        if ($request->has('per_page') && !empty($request->per_page)) {
            $queryParams['per_page'] = $request->per_page;
        }

        // Log what we're sending
        // \Log::info('CompaniesController@show - Building API request', [
        //     'company_id' => $id,
        //     'query_params' => $queryParams,
        //     'all_request_params' => $request->all()
        // ]);

        // Fetch company details with filtered jobs - pass params directly to api() method
        $company = $this->countryService->api('companies/' . $id, $queryParams);

        if (!$company) {
            abort(404, 'Company not found');
        }

        // Jobs are included in the company response with pagination
        $jobs = [
            'data' => $company['jobs'] ?? [],
            'pagination' => $company['pagination'] ?? [
                'current_page' => 1,
                'last_page' => 1,
                'total' => count($company['jobs'] ?? [])
            ]
        ];

        // \Log::info('CompaniesController@show - Jobs result', [
        //     'company_name' => $company['name'] ?? 'Unknown',
        //     'jobs_count' => count($company['jobs'] ?? []),
        //     'total_jobs' => $company['pagination']['total'] ?? 0
        // ]);

        $country = $this->countryService->getCountryData();
        
        // Fetch categories and job types for filters
        $categories = $this->countryService->api('categories', [], 'GET', 3600);
        $jobTypes = $this->countryService->api('job-types', [], 'GET', 3600);
        $locations = $this->countryService->api('locations', [], 'GET', 3600);

        return view('companies.show', compact('company', 'country', 'jobs', 'categories', 'jobTypes', 'locations'));
    }

}