<?php
namespace App\Http\Controllers\Employer;

use App\Http\Controllers\Controller;
use App\Services\CountryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class EmployerProfileController extends Controller
{
    public function __construct(protected CountryService $countryService) {}

    /**
     * Show the company profile page.
     */
    public function index()
    {
        if (!Session::has('user')) {
            return redirect()->route('login');
        }

        $response = $this->countryService->api('employer/profile', [], 'GET', 0, false);

        if (!($response['success'] ?? false)) {
            return redirect()->route('dashboard')
                ->with('error', $response['message'] ?? 'Could not load your profile.');
        }

        $profile = $response['profile'] ?? null;



        // Country dropdown for the address section
        $countriesResponse = $this->countryService->api('countries/active', [], 'GET', 3600, false);
        $countries = $countriesResponse['countries'] ?? [];

        return view('employer.profile.index', compact('profile', 'countries'));
    }

    /**
     * Save profile updates.
     */
    public function update(Request $request)
    {
        if (!Session::has('user')) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }

        $payload = $request->except(['_token', '_method']);

        // Empty strings → null so validation passes
        $payload = collect($payload)->map(function ($value) {
            return $value === '' ? null : $value;
        })->toArray();

        $response = $this->countryService->api(
            'employer/profile',
            $payload,
            'POST',
            0,
            false
        );

        return response()->json($response);
    }

    /**
     * Upload logo (AJAX multipart).
     */
    public function uploadLogo(Request $request)
    {
        if (!Session::has('user')) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }

        $request->validate([
            'logo' => 'required|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $files = ['logo' => $request->file('logo')];

        $response = $this->countryService->api(
            'employer/profile/logo',
            [],
            'POST',
            0,
            false,
            $files
        );

        return response()->json($response);
    }

    /**
     * Remove logo.
     */
    public function deleteLogo()
    {
        if (!Session::has('user')) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }

        $response = $this->countryService->api(
            'employer/profile/logo',
            [],
            'DELETE',
            0,
            false
        );

        return response()->json($response);
    }
}