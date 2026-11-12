<?php

namespace App\Http\Controllers\Home;

use App\Http\Controllers\Controller;
use App\Services\CountryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class CvDownloadController extends Controller
{
    public function __construct(protected CountryService $countryService) {}

    public function download(Request $request)
    {
        if (!Session::has('user')) {
            abort(401, 'Please log in.');
        }

        // ✅ Sanitise the path so a malformed query string can't sneak in
        $path = trim((string) $request->query('path', ''));
        if ($path === '') {
            abort(400, 'Missing file path.');
        }

        // Reject anything that isn't a clean relative path — defense in depth
        if (str_contains($path, '?') || str_contains($path, '..') || str_starts_with($path, '/')) {
            abort(400, 'Invalid file path.');
        }

        $mode = $request->query('mode') === 'view' ? 'view' : 'download';

        // Fetch the file bytes from the main app (API key is in headers, not URL)
        try {
            $response = $this->countryService->stream(
                'auth/user/cv/download',
                ['path' => $path],
                'GET'
            );
        } catch (\Throwable $e) {
            abort(502, 'Could not reach the file server.');
        }

        if (!$response->successful()) {
            abort($response->status() === 404 ? 404 : 502, 'Unable to fetch file.');
        }

        $filename = basename($path);
        $mime     = $response->header('Content-Type') ?: 'application/octet-stream';
        $body     = $response->body();

        if ($mode === 'view') {
            // Display inline — browsers render PDFs
            return response($body, 200, [
                'Content-Type'        => $mime,
                'Content-Disposition' => 'inline; filename="' . $filename . '"',
                'Cache-Control'       => 'private, max-age=60',
            ]);
        }

        return response()->streamDownload(
            fn () => print($body),
            $filename,
            ['Content-Type' => $mime]
        );
    }


}