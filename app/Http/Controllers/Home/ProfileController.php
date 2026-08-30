<?php

namespace App\Http\Controllers\Home;

use App\Http\Controllers\Controller;
use App\Services\CountryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;

class ProfileController extends Controller
{
    protected CountryService $countryService;

    public function __construct(CountryService $countryService)
    {
        $this->countryService = $countryService;
    }

    /**
     * Display the user's profile.
     */
    public function show()
    {
        $user = Session::get('user');
        $token = Session::get('access_token');
        
        if (!$user) {
            return redirect()->route('login')->with('error', 'Please sign in first.');
        }

        // Fetch complete profile data from main app
        $response = $this->countryService->api('auth/user', [], 'GET', 0, false);

        if (isset($response['success']) && $response['success']) {
            $userData = $response['user'];
            // Update session with fresh user data
            Session::put('user', $userData);
            $user = $userData;
        }

        // Determine if user is job seeker or employer
        $role = $user['role'] ?? 'job_seeker';
        $isEmployer = $role === 'employer';
        $isSeeker = $role === 'job_seeker';

        // \Log::info($user);
        return view('profile.show', compact('user', 'isEmployer', 'isSeeker', 'role'));
    }

    /**
     * Update user profile.
     */
    public function update(Request $request)
    {
        $user = Session::get('user');
        $token = Session::get('access_token');

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }

        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'country_code' => 'nullable|string|max:3',
            'bio' => 'nullable|string|max:500',
            'professional_title' => 'nullable|string|max:255',
            'years_of_experience' => 'nullable|integer|min:0',
            'skills' => 'nullable|string',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:255',
            'linkedin_url' => 'nullable|url|max:255',
            'github_url' => 'nullable|url|max:255',
            'portfolio_url' => 'nullable|url|max:255',
            // Add missing fields validation
            'country' => 'nullable|string|max:255',
            'postal_code' => 'nullable|string|max:20',
            'date_of_birth' => 'nullable|date',
            'nationality' => 'nullable|string|max:255',
            'professional_summary' => 'nullable|string|max:2000',
            'languages' => 'nullable|array',
            'work_experience' => 'nullable|array',
            'education' => 'nullable|array',
            'certifications' => 'nullable|array',
            'projects' => 'nullable|array',
            'is_public' => 'nullable|boolean', 
        ]);

        // Prepare data for API - include ALL fields
        $data = [
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'phone' => $request->phone,
            'country_code' => $request->country_code,
            'bio' => $request->bio,
            'professional_title' => $request->professional_title,
            'years_of_experience' => $request->years_of_experience,
            'skills' => $request->skills ? explode(',', $request->skills) : null,
            'address' => $request->address,
            'city' => $request->city,
            'linkedin_url' => $request->linkedin_url,
            'github_url' => $request->github_url,
            'portfolio_url' => $request->portfolio_url,
            // Include the missing fields
            'country' => $request->country,
            'postal_code' => $request->postal_code,
            'date_of_birth' => $request->date_of_birth,
            'nationality' => $request->nationality,
            'professional_summary' => $request->professional_summary,
            'languages' => $request->languages,
            'work_experience' => $request->work_experience,
            'education' => $request->education,
            'certifications' => $request->certifications,
            'projects' => $request->projects,
            'is_public' => $request->has('is_public') ? filter_var($request->is_public, FILTER_VALIDATE_BOOLEAN) : false,
        ];

        // Remove null values
        $data = array_filter($data, function ($value) {
            return !is_null($value) && $value !== '';
        });

        // Send to main app API
        $response = $this->countryService->api('auth/user/update', $data, 'PUT', 0, false);

        if (isset($response['success']) && $response['success']) {
            if (isset($response['user'])) {
                Session::put('user', $response['user']);
            }
            return response()->json([
                'success' => true,
                'message' => 'Profile updated successfully!',
                'user' => $response['user'] ?? null
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => $response['message'] ?? 'Failed to update profile.'
        ], 400);
    }

    /**
     * Upload avatar.
     */
    public function uploadAvatar(Request $request)
    {
        // Log::info('Avatar upload called');
        
        $user = Session::get('user');
        $token = Session::get('access_token');

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }

        try {
            $request->validate([
                'avatar' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:2048'
            ]);

            if (!$request->hasFile('avatar')) {
                return response()->json(['success' => false, 'message' => 'No file uploaded'], 400);
            }

            $file = $request->file('avatar');
            
            // Log::info('File info', [
            //     'name' => $file->getClientOriginalName(),
            //     'size' => $file->getSize(),
            //     'mime' => $file->getMimeType(),
            // ]);

            $response = $this->countryService->api('auth/user/avatar', [], 'POST', 0, false, [
                'avatar' => $file
            ]);

            // Log::info('Avatar upload response', ['response' => $response]);

            if (isset($response['success']) && $response['success']) {
                if (isset($response['user'])) {
                    Session::put('user', $response['user']);
                }
                return response()->json([
                    'success' => true,
                    'message' => 'Avatar updated successfully!',
                    'avatar' => $response['avatar'] ?? ($response['user']['avatar'] ?? null)
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => $response['message'] ?? 'Failed to update avatar.'
            ], 400);

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Avatar validation error', ['errors' => $e->errors()]);
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Avatar upload error', ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to update avatar: ' . $e->getMessage()
            ], 500);
        }
    }


}