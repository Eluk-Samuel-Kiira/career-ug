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

        // Existing CVs from the profile
        $userResp = $this->countryService->api('auth/user', [], 'GET', 0, false);
        $user     = $userResp['user'] ?? [];
        $cvFiles  = $user['cv_files'] ?? [];
        $cvFiles  = is_array($cvFiles) ? $cvFiles : [];

        // Add URLs to each CV
        $cvFiles = array_map(function ($f) {
            if (!empty($f['path'])) {
                $f['url'] = \Illuminate\Support\Facades\Storage::disk('public')->url($f['path']);
            }
            return $f;
        }, $cvFiles);

        // Companies for the picker
        $companies = $this->countryService->api('letters/companies', [], 'GET', 3600, false);

        return view('seeker.letter.create', [
            'cvFiles'   => $cvFiles,
            'companies' => $companies['data'] ?? [],
        ]);
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

    public function pay($uuid)
    {
        if (!Session::has('user')) return response()->json(['success' => false], 401);

        return response()->json(
            $this->countryService->api("letters/{$uuid}/pay", [], 'POST', 0, false)
        );
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
}