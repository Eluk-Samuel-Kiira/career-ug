<?php

namespace App\Http\Controllers\Home;

use App\Http\Controllers\Controller;
use App\Services\CountryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class AcademicDocumentsController extends Controller
{
    public function __construct(protected CountryService $countryService) {}

    public function upload(Request $request)
    {
        if (!Session::has('user')) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }

        $request->validate([
            'document' => 'required|file|mimes:pdf|max:10240',
        ], [
            'document.mimes' => 'Please upload a single PDF file. Merge all your documents into one PDF.',
            'document.max'   => 'The PDF must not exceed 10 MB.',
        ]);

        try {
            $response = $this->countryService->api(
                'auth/user/academic-documents',
                [],
                'POST',
                0,
                false,
                ['document' => $request->file('document')]
            );
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Upload failed: ' . $e->getMessage(),
            ], 500);
        }

        return response()->json($response, $response['status'] ?? 200);
    }

    public function destroy()
    {
        if (!Session::has('user')) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }

        $response = $this->countryService->api('auth/user/academic-documents', [], 'DELETE', 0, false);

        return response()->json($response, $response['status'] ?? 200);
    }

    public function download(Request $request)
    {
        if (!Session::has('user')) {
            abort(401);
        }

        $path = (string) $request->query('path', '');
        $path = preg_replace('/\?.*$/', '', $path);

        if ($path === '' || str_contains($path, '..') || str_starts_with($path, '/')) {
            abort(400, 'Invalid file path.');
        }

        $mode = $request->query('mode') === 'view' ? 'view' : 'download';

        try {
            $response = $this->countryService->stream(
                'auth/user/academic-documents/download',
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
        $mime     = $response->header('Content-Type') ?: 'application/pdf';
        $body     = $response->body();

        if ($mode === 'view') {
            return response($body, 200, [
                'Content-Type'        => $mime,
                'Content-Disposition' => 'inline; filename="' . $filename . '"',
            ]);
        }

        return response()->streamDownload(fn () => print($body), $filename, [
            'Content-Type' => $mime,
        ]);
    }
}