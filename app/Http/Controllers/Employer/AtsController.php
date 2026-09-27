<?php

namespace App\Http\Controllers\Employer;

use App\Http\Controllers\Controller;
use App\Services\CountryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class AtsController extends Controller
{
    public function __construct(protected CountryService $countryService) {}

    public function index($slug)
    {
        if (!Session::has('user')) return redirect()->route('login');

        $response = $this->countryService->api("employer/ats/{$slug}/applicants", [], 'GET', 0, false);

        if (!($response['success'] ?? false)) {
            return redirect()->route('employer.jobs.index')
                ->with('error', $response['message'] ?? 'Could not load applicants.');
        }

        return view('employer.ats.index', [
            'job'        => $response['job'],
            'applicants' => $response['data'] ?? [],
            'counts'     => $response['counts'] ?? [],
            'meta'       => $response['meta'] ?? [],
        ]);
    }

    public function data(Request $request, $slug)
    {
        if (!Session::has('user')) return response()->json(['success' => false], 401);

        return response()->json(
            $this->countryService->api(
                "employer/ats/{$slug}/applicants",
                $request->only(['status', 'search', 'rating', 'page']),
                'GET', 0, false
            )
        );
    }

    public function show($slug, $id)
    {
        if (!Session::has('user')) return response()->json(['success' => false], 401);

        return response()->json(
            $this->countryService->api("employer/ats/{$slug}/applicants/{$id}", [], 'GET', 0, false)
        );
    }

    public function update(Request $request, $slug, $id)
    {
        if (!Session::has('user')) return response()->json(['success' => false], 401);

        return response()->json(
            $this->countryService->api(
                "employer/ats/{$slug}/applicants/{$id}",
                $request->all(), 'PUT', 0, false
            )
        );
    }

    public function bulk(Request $request, $slug)
    {
        if (!Session::has('user')) return response()->json(['success' => false], 401);

        return response()->json(
            $this->countryService->api(
                "employer/ats/{$slug}/applicants/bulk",
                $request->all(), 'POST', 0, false
            )
        );
    }

    public function downloadCv($slug, $id)
    {
        if (!Session::has('user')) abort(401);

        $url = rtrim(config('services.api.base_url', env('API_BASE_URL')), '/')
             . "/employer/ats/{$slug}/applicants/{$id}/cv";

        return redirect()->away($url);
    }

    public function export($slug)
    {
        if (!Session::has('user')) abort(401);

        $url = rtrim(config('services.api.base_url', env('API_BASE_URL')), '/')
             . "/employer/ats/{$slug}/applicants/export";

        return redirect()->away($url);
    }

    public function screen(Request $request, $slug)
    {
        if (!Session::has('user')) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }

        $response = $this->countryService->api(
            "employer/ats/{$slug}/screen",
            $request->only(['applicant_ids', 'force']),
            'POST',
            0,      // no cache
            false,  // don't unwrap
            [],     // no files
            30      // short timeout — the request returns quickly now
        );

        return response()->json($response);
    }

    public function batchStatus($uuid)
    {
        if (!Session::has('user')) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }

        $response = $this->countryService->api(
            "employer/ats/batches/{$uuid}",
            [],
            'GET',
            0,      // ← important: don't cache
            false
        );

        return response()->json($response);
    }

}