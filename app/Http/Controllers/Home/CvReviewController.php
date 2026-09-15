<?php

namespace App\Http\Controllers\Home;

use App\Http\Controllers\Controller;
use App\Services\CountryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;

class CvReviewController extends Controller
{
    public function __construct(protected CountryService $countryService) {}

    /**
     * Landing page — shows the offer + quote + submission form.
     */
    public function index()
    {
        if (!Session::has('user')) {
            return redirect()->route('login')->with('error', 'Please sign in first.');
        }

        // quote (existing)
        $quoteResponse = $this->countryService->api('cv-review/quote', [], 'GET', 0, false);
        $quote = ($quoteResponse['success'] ?? false) && ($quoteResponse['available'] ?? false)
            ? $quoteResponse
            : null;

        // list (existing)
        $requestsResponse = $this->countryService->api('cv-review', [], 'GET', 0, false);
        $requests = $requestsResponse['data'] ?? [];
        $meta     = $requestsResponse['meta'] ?? [
            'total'        => 0,
            'active_count' => 0,
            'max_active'   => 3,
            'can_create'   => true,
        ];

        // CV files (existing)
        $cvResponse = $this->countryService->api('auth/user/cv', [], 'GET', 0, false);
        $cvFiles    = $cvResponse['cv_files'] ?? [];
        $maxFiles   = $cvResponse['max_files'] ?? 3;

        return view('job-seeker.cv-review.index', compact(
            'quote', 'requests', 'meta', 'cvFiles', 'maxFiles'
        ));
    }

    /**
     * Show the submission form for a new request.
     */
    public function create()
    {
        if (!Session::has('user')) {
            return redirect()->route('login');
        }

        $quoteResponse = $this->countryService->api('cv-review/quote', [], 'GET', 0, false);
        if (!($quoteResponse['success'] ?? false)) {
            return redirect()->route('cv-review.index')
                ->with('error', $quoteResponse['message'] ?? 'Service unavailable in your country.');
        }

        $cvResponse = $this->countryService->api('auth/user/cv', [], 'GET', 0, false);
        $cvFiles = $cvResponse['cv_files'] ?? [];

        return view('job-seeker.cv-review.create', [
            'quote'   => $quoteResponse,
            'cvFiles' => $cvFiles,
        ]);
    }

    /**
     * Submit request to the API.
     */
    public function store(Request $request)
    {
        $user = Session::get('user');
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }

        $request->validate([
            'cv_file'                => 'nullable|file|mimes:pdf,doc,docx|max:10240',
            'existing_cv_path'       => 'nullable|string',
            'target_job_title'       => 'nullable|string|max:255',
            'target_job_description' => 'nullable|string|max:10000',
        ]);

        if (!$request->hasFile('cv_file') && !$request->filled('existing_cv_path')) {
            return response()->json([
                'success' => false,
                'message' => 'Please upload a CV or select one of your existing CVs.',
            ], 422);
        }

        // Files need to be sent via the CountryService file support
        $files = [];
        if ($request->hasFile('cv_file')) {
            $files['cv_file'] = $request->file('cv_file');
        }

        $params = array_filter([
            'existing_cv_path'       => $request->input('existing_cv_path'),
            'target_job_title'       => $request->input('target_job_title'),
            'target_job_description' => $request->input('target_job_description'),
        ], fn($v) => $v !== null && $v !== '');

        try {
            $response = $this->countryService->api(
                'cv-review',
                $params,
                'POST',
                0,     
                false,   
                $files,
                180     
            );

            return response()->json($response);

        } catch (\Exception $e) {
            Log::error('CV review submit failed', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to submit: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Show a single request detail (status timeline + AI gap review).
     */
    public function show($uuid)
    {
        if (!Session::has('user')) {
            return redirect()->route('login');
        }

        $response = $this->countryService->api("cv-review/{$uuid}", [], 'GET', 0, false);

        if (!($response['success'] ?? false)) {
            return redirect()->route('cv-review.index')->with('error', 'Request not found.');
        }

        return view('job-seeker.cv-review.show', [
            'cvRequest' => $response['request'],
        ]);
    }

    /**
     * Submit answers to the AI gap questions (AJAX).
     */
    public function submitAnswers(Request $request, $uuid)
    {
        if (!Session::has('user')) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }

        $response = $this->countryService->api(
            "cv-review/{$uuid}/answers",
            ['answers' => $request->input('answers', [])],
            'POST',
            0,
            false
        );

        return response()->json($response);
    }

    /**
     * Trigger payment init and redirect to gateway.
     */
    public function pay(Request $request, $uuid)
    {
        if (!Session::has('user')) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }

        $response = $this->countryService->api(
            "cv-review/{$uuid}/pay",
            [],
            'POST',
            0,
            false
        );

        if (($response['success'] ?? false) && !empty($response['payment_url'])) {
            return response()->json([
                'success'      => true,
                'redirect_url' => $response['payment_url'],
            ]);
        }

        // No gateway URL (dev mode) — just acknowledge
        return response()->json($response);
    }

    /**
     * Request a revision on a delivered CV.
     */
    public function requestRevision(Request $request, $uuid)
    {
        if (!Session::has('user')) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }

        $request->validate([
            'notes' => 'required|string|max:2000',
        ]);

        $response = $this->countryService->api(
            "cv-review/{$uuid}/revision",
            ['notes' => $request->notes],
            'POST',
            0,
            false
        );

        return response()->json($response);
    }


    public function myRequests(Request $request)
    {
        if (!Session::has('user')) {
            return redirect()->route('login')->with('error', 'Please sign in first.');
        }

        $status = $request->get('status', '');

        $query = [];
        if ($status) {
            $query['status'] = $status;
        }

        $response = $this->countryService->api('cv-review', $query, 'GET', 0, false);
        $requests = $response['data'] ?? [];
        $meta     = $response['meta'] ?? [
            'total'        => 0,
            'current_page' => 1,
            'last_page'    => 1,
            'active_count' => 0,
            'max_active'   => 3,
            'can_create'   => true,
        ];

        // Counts per status (fetch full list once, count client-side)
        $allResponse = $this->countryService->api('cv-review', [], 'GET', 0, false);
        $allRequests = $allResponse['data'] ?? [];

        $counts = [
            'all'                => count($allRequests),
            'awaiting_payment'   => 0,
            'in_progress'        => 0,
            'delivered'          => 0,
            'completed'          => 0,
        ];
        foreach ($allRequests as $r) {
            $s = $r['status'] ?? '';
            if (isset($counts[$s])) {
                $counts[$s]++;
            }
            if (in_array($s, ['ai_reviewed'], true)) {
                $counts['awaiting_payment']++;
            }
        }

        // ✅ THIS is the view I rewrote
        return view('job-seeker.cv-review.my-requests', compact(
            'requests', 'meta', 'counts', 'status'
        ));
    }

    /**
     * Delete a CV review request (proxies to API).
     */
    public function destroy($uuid)
    {
        if (!Session::has('user')) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }

        try {
            $response = $this->countryService->api(
                "cv-review/{$uuid}",
                [],
                'DELETE',
                0,
                false
            );

            return response()->json($response);

        } catch (\Throwable $e) {
            Log::error('Failed to proxy delete CV review request', [
                'uuid'  => $uuid,
                'error' => $e->getMessage(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete: ' . $e->getMessage(),
            ], 500);
        }
    }


}