<?php

namespace App\Http\Controllers\Employer;

use App\Http\Controllers\Controller;
use App\Services\CountryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class HiringController extends Controller
{
    public function __construct(protected CountryService $countryService) {}

    public function index()
    {
        if (!Session::has('user')) return redirect()->route('login');

        $response = $this->countryService->api('employer/hiring', [], 'GET', 0, false);

        return view('employer.hiring.index', [
            'runs' => $response['data']['data'] ?? ($response['data'] ?? []),
        ]);
    }

    public function myJobPosts()
    {
        if (!Session::has('user')) {
            return response()->json(['success' => false], 401);
        }

        return response()->json(
            $this->countryService->api('employer/hiring/my-job-posts', [], 'GET', 0, false)
        );
    }

    public function create()
    {
        if (!Session::has('user')) return redirect()->route('login');

        return view('employer.hiring.create');
    }

    public function store(Request $request)
    {
        if (!Session::has('user')) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }

        $params = $request->only(['title', 'job_description', 'job_post_id']);

        $files = [];

        // Job description file (single)
        if ($request->hasFile('job_description_file')) {
            $files['job_description_file'] = $request->file('job_description_file');
        }

        // CVs — array input. hasFile('cvs') is false when the field is cvs[]
        // because the browser sends the array. Read both keys.
        $cvFiles = $request->file('cvs') ?? $request->file('cvs[]') ?? [];
        if (!is_array($cvFiles)) {
            $cvFiles = [$cvFiles];
        }
        foreach ($cvFiles as $index => $file) {
            if ($file instanceof \Illuminate\Http\UploadedFile && $file->isValid()) {
                $files["cvs[{$index}]"] = $file;
            }
        }

        // Zip (single)
        if ($request->hasFile('cvs_zip')) {
            $files['cvs_zip'] = $request->file('cvs_zip');
        }

        // \Log::info('[Proxy hiring store] forwarding', [
        //     'params'    => $params,
        //     'file_keys' => array_keys($files),
        //     'cvs_count' => count($cvFiles),
        // ]);

        $response = $this->countryService->api('employer/hiring', $params, 'POST', 0, false, $files);

        $status = ($response['success'] ?? false) ? 200 : ($response['status'] ?? 422);
        return response()->json($response, $status);
    }

    public function status($uuid)
    {
        if (!Session::has('user')) return response()->json(['success' => false], 401);

        return response()->json(
            $this->countryService->api("employer/hiring/{$uuid}/status", [], 'GET', 0, false)
        );
    }

    public function results(Request $request, $uuid)
    {
        if (!Session::has('user')) return response()->json(['success' => false], 401);

        return response()->json(
            $this->countryService->api(
                "employer/hiring/{$uuid}/results",
                $request->only(['search', 'recommendation', 'min_score', 'sort', 'page']),
                'GET',
                0,
                false
            )
        );
    }

    public function show($uuid)
    {
        if (!Session::has('user')) return redirect()->route('login');

        return view('employer.hiring.show', ['uuid' => $uuid]);
    }

    public function export($uuid)
    {
        if (!Session::has('user')) abort(401);

        $url = rtrim(config('app.main_app_url'), '/')
             . "/api/employer/hiring/{$uuid}/export"
             . '?api_key=' . config('app.main_app_api_key')
             . '&country_code=' . config('app.country_code');

        return redirect()->away($url);
    }

    public function destroy($uuid)
    {
        if (!Session::has('user')) return response()->json(['success' => false], 401);

        return response()->json(
            $this->countryService->api("employer/hiring/{$uuid}", [], 'DELETE', 0, false)
        );
    }
}