<?php

namespace App\Http\Controllers\Employer;

use App\Http\Controllers\Controller;
use App\Services\CountryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class ComplianceController extends Controller
{
    public function __construct(protected CountryService $countryService) {}

    /**
     * Show the compliance page.
     */
    public function index()
    {
        if (!Session::has('user')) {
            return redirect()->route('login');
        }

        $response = $this->countryService->api('employer/documents', [], 'GET', 0, false);

        if (!($response['success'] ?? false)) {
            return redirect()->route('dashboard')
                ->with('error', $response['message'] ?? 'Could not load compliance data.');
        }

        $data = $response['data'] ?? [];

        return view('employer.compliance.index', compact('data'));
    }

    /**
     * Upload a document.
     */
    public function upload(Request $request, string $type)
    {
        if (!Session::has('user')) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }

        $request->validate([
            'file'       => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'expires_at' => 'nullable|date',
        ]);

        $files = ['file' => $request->file('file')];

        $response = $this->countryService->api(
            "employer/documents/{$type}",
            [
                'expires_at' => $request->input('expires_at'),
                'issued_at'  => $request->input('issued_at'),
            ],
            'POST',
            0,
            false,
            $files
        );

        return response()->json($response);
    }

    /**
     * Delete a document.
     */
    public function destroy(string $type)
    {
        if (!Session::has('user')) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }

        $response = $this->countryService->api(
            "employer/documents/{$type}",
            [],
            'DELETE',
            0,
            false
        );

        return response()->json($response);
    }

    /**
     * Submit for review.
     */
    public function submit()
    {
        if (!Session::has('user')) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }

        $response = $this->countryService->api(
            'employer/documents/submit',
            [],
            'POST',
            0,
            false
        );

        return response()->json($response);
    }
}