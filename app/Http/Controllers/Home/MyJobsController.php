<?php

namespace App\Http\Controllers\Home;

use App\Http\Controllers\Controller;
use App\Services\CountryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;

class MyJobsController extends Controller
{
    public function __construct(protected CountryService $countryService) {}

    /**
     * Legacy landing — redirect to the category tab.
     */
    public function index()
    {
        if (!Session::has('user')) {
            return redirect()->route('login')->with('error', 'Please sign in first.');
        }
        return redirect()->route('my-jobs.category');
    }

    /**
     * "By Category" page.
     */
    public function byCategory()
    {
        if (!Session::has('user')) {
            return redirect()->route('login')->with('error', 'Please sign in first.');
        }

        return view('job-seeker.my-jobs', [
            'tab' => 'category',
        ]);
    }

    /**
     * "By Skills" page.
     */
    public function bySkills()
    {
        if (!Session::has('user')) {
            return redirect()->route('login')->with('error', 'Please sign in first.');
        }

        return view('job-seeker.my-jobs', [
            'tab' => 'skills',
        ]);
    }

    /**
     * JSON data for the current page (same API, both tabs use it).
     */
    public function data(Request $request)
    {
        if (!Session::has('user')) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }

        $params = array_filter([
            'cat_page'   => $request->get('cat_page'),
            'skill_page' => $request->get('skill_page'),
        ], fn ($v) => $v !== null && $v !== '');

        try {
            $response = $this->countryService->api('my-jobs', $params, 'GET', 0, false);
            return response()->json($response);
        } catch (\Throwable $e) {
            Log::error('[my-jobs] proxy failed', ['error' => $e->getMessage()]);
            return response()->json([
                'success'    => false,
                'configured' => false,
                'message'    => 'Could not load your matched jobs.',
            ], 500);
        }
    }
}