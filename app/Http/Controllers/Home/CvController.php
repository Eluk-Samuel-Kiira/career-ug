<?php

namespace App\Http\Controllers\Home;

use App\Http\Controllers\Controller;
use App\Services\CountryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;

class CvController extends Controller
{
    public function __construct(protected CountryService $countryService) {}

    public function edit()
    {
        if (!Session::has('user')) {
            return redirect()->route('login')->with('error', 'Please sign in first.');
        }

        // Get user data
        $response = $this->countryService->api('auth/user', [], 'GET', 0, false);
        $profile = $response['user'] ?? Session::get('user');

        // Get CV files list
        $cvResponse = $this->countryService->api('auth/user/cv', [], 'GET', 0, false);
        
        // Log::info('CV Response from API', ['cvResponse' => $cvResponse]);
        
        // Ensure cv_files is always an array
        $cvFiles = $cvResponse['cv_files'] ?? [];
        if (!is_array($cvFiles)) {
            $cvFiles = [];
        }
        
        $maxFiles = $cvResponse['max_files'] ?? 3;
        $totalCount = count($cvFiles);

        // Log::info('CV Files count', ['total' => $totalCount, 'files' => $cvFiles]);

        return view('job-seeker.cv.edit', compact('profile', 'cvFiles', 'maxFiles', 'totalCount'));
    }

    public function upload(Request $request)
    {
        $request->validate([
            'cv' => 'required|file|mimes:pdf,doc,docx|max:5120',
        ]);

        $user = Session::get('user');
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }

        try {
            $file = $request->file('cv');
            
            // Use 180 second timeout for CV processing
            $response = $this->countryService->api(
                'auth/user/cv',
                [], // params
                'POST', // method
                0, // cache minutes
                false, // unwrap data
                ['cv' => $file], // files
                180 // timeout in seconds
            );

            // Log::info('CV upload response', ['response' => $response]);

            if (isset($response['success']) && $response['success']) {
                if (isset($response['profile'])) {
                    // Update session with profile data
                    $userData = Session::get('user');
                    $userData = array_merge($userData, $response['profile'] ?? []);
                    Session::put('user', $userData);
                }
                
                return response()->json([
                    'success' => true,
                    'message' => $response['message'] ?? 'CV uploaded successfully!',
                    'profile' => $response['profile'] ?? null,
                    'cv_files' => $response['cv_files'] ?? [],
                    'total_count' => $response['total_count'] ?? 0,
                    'max_files' => $response['max_files'] ?? 3,
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => $response['message'] ?? 'Failed to upload CV.'
            ], 422);

        } catch (\Exception $e) {
            Log::error('CV upload error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to upload CV: ' . $e->getMessage()
            ], 422);
        }
    }

    public function delete(Request $request)
    {
        $request->validate([
            'file_path' => 'required|string',
        ]);

        $user = Session::get('user');
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }

        try {
            $response = $this->countryService->api(
                'auth/user/cv',
                ['file_path' => $request->file_path],
                'DELETE',
                0,
                false
            );

            if (isset($response['success']) && $response['success']) {
                return response()->json([
                    'success' => true,
                    'message' => $response['message'] ?? 'CV deleted successfully!',
                    'cv_files' => $response['cv_files'] ?? [],
                    'total_count' => $response['total_count'] ?? 0,
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => $response['message'] ?? 'Failed to delete CV.'
            ], 422);

        } catch (\Exception $e) {
            Log::error('CV delete error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete CV: ' . $e->getMessage()
            ], 422);
        }
    }
}