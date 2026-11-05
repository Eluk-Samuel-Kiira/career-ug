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

        return redirect()->route('cv-review.my');
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

        // If the quote isn't available, bounce the user back to the index
        // with a flash message instead of letting the Blade blow up.
        if (
            !is_array($quoteResponse)
            || !($quoteResponse['success'] ?? false)
            || !($quoteResponse['available'] ?? false)
            || !isset($quoteResponse['service'], $quoteResponse['price'])
        ) {
            return redirect()
                ->route('cv-review.my')
                ->with('error', $quoteResponse['message'] ?? 'CV Review is currently unavailable in your region.');
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
            'job_source'             => 'nullable|in:db,manual,paste,none',
            'job_post_id'            => 'nullable|integer',
            'target_job_title'       => 'nullable|string|max:255',
            'target_company_name'    => 'nullable|string|max:255',
            'target_job_description' => 'nullable|string|max:20000',
        ]);

        if (!$request->hasFile('cv_file') && !$request->filled('existing_cv_path')) {
            return response()->json([
                'success' => false,
                'message' => 'Please upload a CV or select one of your existing CVs.',
            ], 422);
        }

        $files = [];
        if ($request->hasFile('cv_file')) {
            $files['cv_file'] = $request->file('cv_file');
        }

        // ── IMPORTANT ─────────────────────────────────────────────
        // Forward every job-picker field. array_filter with the strict
        // callback keeps '0', false, and integers intact — only null
        // and '' are dropped.
        // ──────────────────────────────────────────────────────────
        $params = array_filter([
            'existing_cv_path'       => $request->input('existing_cv_path'),
            'job_source'             => $request->input('job_source', 'none'),
            'job_post_id'            => $request->input('job_post_id'),
            'target_job_title'       => $request->input('target_job_title'),
            'target_company_name'    => $request->input('target_company_name'),
            'target_job_description' => $request->input('target_job_description'),
        ], fn($v) => $v !== null && $v !== '');

        try {
            // \Log::info('CV review proxy — forwarding params', [
            //     'keys'        => array_keys($params),
            //     'job_source'  => $params['job_source']  ?? null,
            //     'job_post_id' => $params['job_post_id'] ?? null,
            //     'title'       => $params['target_job_title'] ?? null,
            // ]);

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
     * Proxy job search to the API (used by the CV review create page).
     */
    public function searchJobs(Request $request)
    {
        if (!Session::has('user')) {
            return response()->json(['success' => false, 'data' => []], 401);
        }

        $q = trim((string) $request->get('q', ''));
        if (mb_strlen($q) < 2) {
            return response()->json(['success' => true, 'data' => []]);
        }

        $response = $this->countryService->api(
            'cv-review/search-jobs',
            ['q' => $q],
            'GET',
            0,
            false
        );

        return response()->json($response);
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
            return redirect()->route('cv-review.my')->with('error', 'Request not found.');
        }

        $r = $response['request'] ?? [];

        // ── LOG WHAT THE PROXY SEES ─────────────────────────────
        Log::info('[show] CV request fetched from API', [
            'uuid'                  => $uuid,
            'status'                => $r['status'] ?? null,
            'rewrite_mode'          => $r['rewrite_mode'] ?? null,
            'rewrite_is_ready'      => $r['rewrite_is_ready'] ?? false,
            'rewrite_generated_at'  => $r['rewrite_generated_at'] ?? null,
            'rewrite_error_message' => $r['rewrite_error_message'] ?? null,
        ]);

        return view('job-seeker.cv-review.show', [
            'cvRequest' => $r,
        ]);
    }

    public function status($uuid)
    {
        if (!Session::has('user')) {
            return response()->json(['success' => false], 401);
        }

        $response = $this->countryService->api("cv-review/{$uuid}", [], 'GET', 0, false);

        if (!($response['success'] ?? false)) {
            return response()->json(['success' => false, 'status' => null], 200);
        }

        $request = $response['request'] ?? [];

        return response()->json([
            'success'        => true,
            'status'         => $request['status']                    ?? null,
            'is_paid'        => $request['is_paid']                   ?? false,
            'payment_ref'    => $request['payment_reference']         ?? null,
            'paid_at'        => $request['paid_at']                   ?? null,
            'rewrite_mode'   => $request['rewrite_mode']              ?? null,
            'rewrite_ready'  => $request['rewrite_is_ready']          ?? false,
            'error_message'  => $request['rewrite_error_message']     ?? null,
        ]);
    }

    public function promoteRewriteOriginal($uuid)
    {
        if (!Session::has('user')) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }

        $response = $this->countryService->api(
            "cv-review/{$uuid}/rewrite/promote-original",
            [],
            'POST',
            0,
            false
        );

        return response()->json($response);
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
            [
                'phone'  => $request->input('phone'),
                'method' => $request->input('method', 'mobile_money'),
            ],
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

    public function runAiReview($uuid)
    {
        if (!Session::has('user')) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }

        $response = $this->countryService->api(
            "cv-review/{$uuid}/run-ai-review",
            [],
            'POST',
            0,
            false
        );

        return response()->json($response);
    }

    public function completeRewrite($uuid)
    {
        if (!Session::has('user')) {
            return response()->json(['success' => false], 401);
        }
        $data = $this->countryService->api(
            "cv-review/{$uuid}/rewrite/complete",
            [],
            'POST',
            0,
            false
        );
        return response()->json($data);
    }

    public function showRewrite($uuid)
    {
        if (!Session::has('user')) return response()->json(['success' => false], 401);

        $data = $this->countryService->api("cv-review/{$uuid}/rewrite", [], 'GET', 0, false);
        return response()->json($data);
    }

    public function regenerateRewrite($uuid)
    {
        if (!Session::has('user')) return response()->json(['success' => false], 401);

        $data = $this->countryService->api("cv-review/{$uuid}/rewrite/regenerate", [], 'POST', 0, false);
        return response()->json($data);
    }

    public function saveRewrite(Request $request, $uuid)
    {
        if (!Session::has('user')) return response()->json(['success' => false], 401);

        $data = $this->countryService->api(
            "cv-review/{$uuid}/rewrite/save",
            ['content' => $request->input('content')],
            'POST',
            0,
            false
        );
        return response()->json($data);
    }

    public function resetRewrite($uuid)
    {
        if (!Session::has('user')) return response()->json(['success' => false], 401);

        $data = $this->countryService->api("cv-review/{$uuid}/rewrite/reset", [], 'POST', 0, false);
        return response()->json($data);
    }

    public function downloadRewrite($uuid)
    {
        if (!Session::has('user')) abort(401);

        $response = $this->countryService->stream("cv-review/{$uuid}/rewrite/download");
        if (!$response->successful()) abort($response->status(), 'Not ready');

        $filename = $this->extractFilenameFromDisposition(
            $response->header('Content-Disposition')
        ) ?: ('cv-' . $uuid . '.pdf');

        return response()->streamDownload(function () use ($response) {
            echo $response->body();
        }, $filename, [
            'Content-Type' => $response->header('Content-Type') ?: 'application/pdf',
        ]);
    }

    public function rewriteStatus($uuid)
    {
        if (!Session::has('user')) return response()->json(['success' => false], 401);

        $data = $this->countryService->api("cv-review/{$uuid}/rewrite", [], 'GET', 0, false);
        if (!($data['success'] ?? false)) {
            return response()->json(['success' => false], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => [
                'status'            => $data['data']['status'] ?? null,
                'is_ready'          => $data['data']['is_ready'] ?? false,
                'can_regenerate'    => $data['data']['can_regenerate'] ?? false,
                'error_message'     => $data['data']['error_message'] ?? null,
            ],
        ]);
    }

    protected function extractFilenameFromDisposition(?string $header): ?string
    {
        if (empty($header)) {
            return null;
        }

        // RFC 5987 form: filename*=UTF-8''my_file.pdf
        if (preg_match("/filename\*\s*=\s*UTF-8''([^;]+)/i", $header, $m)) {
            $decoded = rawurldecode(trim($m[1], "\\'\""));
            if ($decoded !== '') {
                return $this->sanitizeFilename($decoded);
            }
        }

        // Plain form: filename="my_file.pdf" or filename=my_file.pdf
        if (preg_match('/filename\s*=\s*"?([^";]+)"?/i', $header, $m)) {
            $raw = trim($m[1]);
            if ($raw !== '') {
                return $this->sanitizeFilename($raw);
            }
        }

        return null;
    }

    protected function sanitizeFilename(string $name): string
    {
        $name = basename($name);
        $name = preg_replace('/[\x00-\x1F\x7F<>:"\/\\\\|?*]+/', '', $name);
        $name = trim($name);

        if ($name === '' || $name === '.' || $name === '..') return 'cv.pdf';
        if (!str_ends_with(strtolower($name), '.pdf')) $name .= '.pdf';

        return mb_substr($name, 0, 200) . '.pdf';
    }


    public function kick($uuid)
    {
        if (!Session::has('user')) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }

        $response = $this->countryService->api(
            "cv-review/{$uuid}/kick",
            [],
            'POST',
            0,
            false
        );

        Log::info('[proxy kick] dispatched', [
            'uuid' => $uuid,
            'response' => $response,
        ]);

        return response()->json($response);
    }


}