<?php

namespace App\Http\Controllers\Employer;

use App\Http\Controllers\Controller;
use App\Services\CountryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class CvFilterController extends Controller
{
    protected CountryService $countryService;

    public function __construct(CountryService $countryService)
    {
        $this->countryService = $countryService;
    }

    /**
     * Subscription gate (stub).
     * Replace with a real check when billing ships.
     */
    protected function hasActiveSubscription(): bool
    {
        // TODO: replace with real subscription check
        return true;
    }

    public function index()
    {
        if (!$this->hasActiveSubscription()) {
            return view('employer.cv-filter.locked');
        }

        return view('employer.cv-filter.index', [
            'filters'   => $this->filters(),
            'isEmployer'=> true,
        ]);
    }

    /**
     * GET /employer/cv-filter/data — proxies to main app
     */
    public function data(Request $request)
    {
        if (!$this->hasActiveSubscription()) {
            return response()->json(['success' => false, 'message' => 'Subscription required'], 402);
        }

        $params = array_filter([
            'search'              => $request->get('search'),
            'job_category_id'     => $request->get('job_category_id'),
            'industry_id'         => $request->get('industry_id'),
            'job_type_id'         => $request->get('job_type_id'),
            'job_location_id'     => $request->get('job_location_id'),
            'experience_level_id' => $request->get('experience_level_id'),
            'education_level_id'  => $request->get('education_level_id'),
            'salary_range_id'     => $request->get('salary_range_id'),
            'min_experience'      => $request->get('min_experience'),
            'has_cv'              => $request->get('has_cv'),
            'profile_complete'    => $request->get('profile_complete'),
            'page'                => $request->get('page', 1),
            'per_page'            => $request->get('per_page', 15),
        ], fn($v) => $v !== null && $v !== '');

        $response = $this->countryService->api('seekers/filter', $params, 'GET', 0, false);

        if (!is_array($response) || ($response['success'] ?? false) === false) {
            return response()->json([
                'success' => false,
                'message' => $response['message'] ?? 'Failed to load candidates',
            ], 400);
        }

        // Transform for the proxy view
        $data = $response['data'] ?? [];
        $meta = $response['meta'] ?? [];

        return response()->json([
            'success' => true,
            'data'    => array_map(fn($s) => $this->transformRow($s), $data),
            'meta'    => $meta,
        ]);
    }

    /**
     * GET /employer/cv-filter/{id} — returns HTML for the modal
     */
    public function show($id)
    {
        if (!$this->hasActiveSubscription()) {
            abort(402);
        }

        $response = $this->countryService->api("seekers/{$id}", [], 'GET', 0, false);

        if (!is_array($response) || ($response['success'] ?? false) === false) {
            abort(404, 'Seeker not found');
        }

        return view('employer.cv-filter.partials.details', [
            'seeker' => $response['data'],
        ]);
    }

    

    // ─────────────────────────────────────────────────────────────
    // Helpers
    // ─────────────────────────────────────────────────────────────

    private function filters(): array
    {
        $cacheKey = 'employer.filters.' . $this->countryService->getCode();

        return \Illuminate\Support\Facades\Cache::remember($cacheKey, now()->addHour(), function () {
            $data = $this->countryService->api('filters/dropdowns', [], 'GET', 3600);

            if (!is_array($data) || ($data['success'] ?? true) === false) {
                return [
                    'categories'        => [],
                    'industries'        => [],
                    'job_types'         => [],
                    'locations'         => [],
                    'experience_levels' => [],
                    'education_levels'  => [],
                    'salary_ranges'     => [],
                ];
            }
            return $data;
        });
    }

    /**
     * Add badge markup to the row before handing to blade/js.
     */
    private function transformRow(array $s): array
    {
        $s['cv_badge'] = $s['has_cv']
            ? '<span class="badge badge-light-success">' . ($s['cv_count'] ?: 1) . ' CV</span>'
            : '<span class="badge badge-light-danger">No CV</span>';

        $s['profile_badge'] = ($s['profile_complete'] ?? false)
            ? '<span class="badge badge-light-success">Complete</span>'
            : '<span class="badge badge-light-warning">Incomplete</span>';

        return $s;
    }
}