<?php

namespace App\Http\Controllers\Seeker;

use App\Http\Controllers\Controller;
use App\Services\CountryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class LetterController extends Controller
{
    public function __construct(protected CountryService $countryService) {}

    protected function requireAuth()
    {
        if (!Session::has('user')) {
            return redirect()->route('login');
        }
        return null;
    }

    public function index()
    {
        if ($r = $this->requireAuth()) return $r;

        $response = $this->countryService->api('letters', [], 'GET', 0, false);

        return view('seeker.letter.index', [
            'letters' => $response['data'] ?? [],
        ]);
    }

    public function create()
    {
        if ($r = $this->requireAuth()) return $r;

        // Existing CVs
        $userResp = $this->countryService->api('auth/user', [], 'GET', 0, false);
        $user     = $userResp['user'] ?? [];
        $cvFiles  = $user['cv_files'] ?? [];
        $cvFiles  = is_array($cvFiles) ? $cvFiles : [];

        $cvFiles = array_map(function ($f) {
            if (!empty($f['path'])) {
                $f['url'] = \Illuminate\Support\Facades\Storage::disk('public')->url($f['path']);
            }
            return $f;
        }, $cvFiles);

        // Companies
        $companies = $this->countryService->api('letters/companies', [], 'GET', 3600, false);

        // ── Detect any in-progress letter so we can lock the UI ──
        $inProgress = null;
        $lettersResp = $this->countryService->api('letters', [], 'GET', 0, false);
        $letters = $lettersResp['data'] ?? [];

        foreach ($letters as $l) {
            $status = $l['status'] ?? null;

            // pending_payment letters are ABANDONED — let user create a fresh one
            // paid/processing = actively generating → LOCK
            if (in_array($status, ['paid', 'processing'], true)) {
                $inProgress = [
                    'uuid'        => $l['uuid'] ?? null,
                    'status'      => $status,
                    'job_title'   => $l['job_title'] ?? null,
                    'company'     => $l['company_name'] ?? null,
                    'created_at'  => $l['created_at'] ?? null,
                ];
                break;
            }
        }

        return view('seeker.letter.create', [
            'cvFiles'    => $cvFiles,
            'companies'  => $companies['data'] ?? [],
            'inProgress' => $inProgress,
        ]);
    }

    public function pay(Request $request, $uuid)
    {
        if (!Session::has('user')) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }

        return response()->json(
            $this->countryService->api(
                "letters/{$uuid}/pay",
                [
                    'phone'  => $request->input('phone'),
                    'method' => $request->input('method', 'mobile_money'),
                ],
                'POST',
                0,
                false
            )
        );
    }

    public function searchJobs(Request $request)
    {
        if (!Session::has('user')) return response()->json(['success' => false], 401);

        return response()->json(
            $this->countryService->api('letters/search-jobs', [
                'q' => $request->get('q', ''),
            ], 'GET', 0, false)
        );
    }

    public function store(Request $request)
    {
        if (!Session::has('user')) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }

        // Build the params — the API expects either existing path OR an uploaded file
        $params = $request->only([
            'cv_source',
            'cv_path',
            'job_source',
            'job_post_id',
            'job_title',
            'company_name',
            'job_description',
            'letter_type',
        ]);

        $files = [];
        if ($request->hasFile('cv_file')) {
            $files['cv_file'] = $request->file('cv_file');
        }

        $response = $this->countryService->api(
            'letters',
            $params,
            'POST',
            0,
            false,
            $files
        );

        $status = ($response['success'] ?? false) ? 200 : ($response['status'] ?? 422);

        return response()->json($response, $status);
    }

    public function saveEdited(Request $request, $uuid)
    {
        if (!Session::has('user')) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }

        return response()->json(
            $this->countryService->api(
                "letters/{$uuid}/save-edited",
                ['content' => $request->input('content')],
                'POST',
                0,
                false
            )
        );
    }

    public function resetEdited(Request $request, $uuid)
    {
        if (!Session::has('user')) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }

        return response()->json(
            $this->countryService->api("letters/{$uuid}/reset-edited", [], 'POST', 0, false)
        );
    }

    public function regenerate(Request $request, $uuid)
    {
        if (!Session::has('user')) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }

        return response()->json(
            $this->countryService->api("letters/{$uuid}/regenerate", [], 'POST', 0, false)
        );
    }

    public function updateInputs(Request $request, $uuid)
    {
        if (!Session::has('user')) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }

        $files = [];
        if ($request->hasFile('cv_file')) {
            $files['cv_file'] = $request->file('cv_file');
        }

        $params = array_filter([
            'job_title'    => $request->input('job_title'),
            'company_name' => $request->input('company_name'),
        ], fn($v) => $v !== null && $v !== '');

        $response = $this->countryService->api(
            "letters/{$uuid}/update",
            $params,
            'POST',
            0,
            false,
            $files
        );

        $status = ($response['success'] ?? false) ? 200 : ($response['status'] ?? 422);

        return response()->json($response, $status);
    }


    public function show($uuid)
    {
        if ($r = $this->requireAuth()) return $r;

        $response = $this->countryService->api("letters/{$uuid}", [], 'GET', 0, false);

        if (!($response['success'] ?? false)) {
            return redirect()->route('letters.index')->with('error', 'Letter not found.');
        }

        return view('seeker.letter.show', [
            'letter' => $response['data'] ?? [],
        ]);
    }

    public function download($uuid)
    {
        if (!Session::has('user')) abort(401);

        $response = $this->countryService->stream("letters/{$uuid}/download");

        if (!$response->successful()) {
            abort($response->status(), 'Letter not ready');
        }

        $filename = 'letter-' . $uuid . '.pdf';

        return response()->streamDownload(function () use ($response) {
            echo $response->body();
        }, $filename, [
            'Content-Type' => $response->header('Content-Type') ?: 'application/pdf',
        ]);
    }

    public function destroy($uuid)
    {
        if (!Session::has('user')) return response()->json(['success' => false], 401);

        return response()->json(
            $this->countryService->api("letters/{$uuid}", [], 'DELETE', 0, false)
        );
    }

    /**
     * Lightweight JSON status for polling — no view, no full content.
     */
    public function status($uuid)
    {
        if (!Session::has('user')) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }

        $response = $this->countryService->api("letters/{$uuid}", [], 'GET', 0, false);

        if (!($response['success'] ?? false)) {
            return response()->json(['success' => false, 'message' => 'Letter not found'], 404);
        }

        $letter = $response['data'] ?? [];

        return response()->json([
            'success' => true,
            'data'    => [
                'uuid'          => $letter['uuid']          ?? $uuid,
                'status'        => $letter['status']        ?? null,
                'is_paid'       => $letter['is_paid']       ?? false,
                'generated_at'  => $letter['generated_at']  ?? null,
                'paid_at'       => $letter['paid_at']       ?? null,
                'error_message' => $letter['error_message'] ?? null,
            ],
        ]);
    }


}