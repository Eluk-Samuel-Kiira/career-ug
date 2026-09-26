<?php

namespace App\Http\Controllers\Employer;

use App\Http\Controllers\Controller;
use App\Services\CountryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class JobSubmissionController extends Controller
{
    public function __construct(protected CountryService $countryService) {}

    /**
     * List page — "My Job Posts".
     */
    public function index(Request $request)
    {
        if (!Session::has('user')) {
            return redirect()->route('login');
        }

        $status = $request->get('status', '');

        // ── Main filtered list (paginated) ──────────────────────────────
        $query = [];
        if ($status) $query['status'] = $status;

        $response = $this->countryService->api('employer/job-submissions', $query, 'GET', 0, false);

        if (!($response['success'] ?? false)) {
            return redirect()->route('dashboard')
                ->with('error', $response['message'] ?? 'Could not load submissions.');
        }

        $submissions = $response['data'] ?? [];
        $meta        = $response['meta'] ?? ['total' => 0, 'current_page' => 1, 'last_page' => 1];

        // ── Counts for tabs & stat cards (single cheap query on API side) ──
        $countsResponse = $this->countryService->api('employer/job-submissions/counts', [], 'GET', 0, false);

        $counts = $countsResponse['counts'] ?? [
            'all'             => 0,
            'draft'           => 0,
            'pending_payment' => 0,
            'pending_review'  => 0,
            'published'       => 0,
            'rejected'        => 0,
            'cancelled'       => 0,
        ];

        return view('employer.jobs.index', compact('submissions', 'meta', 'counts', 'status'));
    }


    /**
     * Package picker page.
     */
    public function create()
    {
        if (!Session::has('user')) {
            return redirect()->route('login');
        }

        $response = $this->countryService->api('employer/job-packages', [], 'GET', 0, false);

        if (!($response['success'] ?? false)) {
            return redirect()->route('employer.jobs.index')
                ->with('error', $response['message'] ?? 'Could not load packages.');
        }

        $packages = $response['packages'] ?? [];
        $countryCode = $response['country_code'] ?? 'XX';

        return view('employer.jobs.create', compact('packages', 'countryCode'));
    }

    /**
     * Store a new submission.
     */
    public function store(Request $request)
    {
        if (!Session::has('user')) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }

        $request->validate([
            'service_key' => 'required|string',
            'job_title'   => 'required|string|max:255',
            'content'     => 'required|string|min:30|max:50000',
        ]);

        $payload = [
            'service_key'    => $request->input('service_key'),
            'job_title'      => $request->input('job_title'),
            'content'        => $request->input('content'),
            'target_country' => $request->input('target_country'),
        ];

        $response = $this->countryService->api('employer/job-submissions', $payload, 'POST', 0, false);

        return response()->json($response);
    }

    /**
     * Show a single submission.
     */
    public function show($uuid)
    {
        if (!Session::has('user')) {
            return redirect()->route('login');
        }

        $response = $this->countryService->api("employer/job-submissions/{$uuid}", [], 'GET', 0, false);

        if (!($response['success'] ?? false)) {
            return redirect()->route('employer.jobs.index')
                ->with('error', $response['message'] ?? 'Submission not found.');
        }

        $submission = $response['submission'];

        return view('employer.jobs.show', compact('submission'));
    }

    /**
     * Record a payment reference.
     */
    public function recordPayment(Request $request, $uuid)
    {
        if (!Session::has('user')) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }

        $request->validate([
            'payment_reference' => 'required|string|max:255',
        ]);

        $response = $this->countryService->api(
            "employer/job-submissions/{$uuid}/payment",
            ['payment_reference' => $request->input('payment_reference')],
            'POST',
            0,
            false
        );

        return response()->json($response);
    }

    /**
     * Cancel a submission.
     */
    public function cancel($uuid)
    {
        if (!Session::has('user')) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }

        $response = $this->countryService->api(
            "employer/job-submissions/{$uuid}/cancel",
            [],
            'POST',
            0,
            false
        );

        return response()->json($response);
    }

    /**
     * Delete a submission.
     */
    public function destroy($uuid)
    {
        if (!Session::has('user')) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }

        $response = $this->countryService->api(
            "employer/job-submissions/{$uuid}",
            [],
            'DELETE',
            0,
            false
        );

        return response()->json($response);
    }
}