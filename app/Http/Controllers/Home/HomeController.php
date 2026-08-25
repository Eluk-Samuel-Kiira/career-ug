<?php

namespace App\Http\Controllers\Home;

use App\Http\Controllers\Controller;
use App\Services\CountryService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HomeController extends Controller
{
    protected CountryService $countryService;

    public function __construct(CountryService $countryService)
    {
        $this->countryService = $countryService;
    }

    public function index() : View
    {
        // Fetch featured jobs from the API
        $featuredJobs = $this->countryService->api('jobs', ['featured' => true, 'per_page' => 3], 'GET', 300, false);
        
        // If featured jobs are empty, try to get any active jobs as fallback
        if (empty($featuredJobs['data']) || count($featuredJobs['data']) === 0) {
            $featuredJobs = $this->countryService->api('jobs', ['per_page' => 3, 'sort' => 'newest'], 'GET', 300, false);
        }

        return view('home.home', compact('featuredJobs'));
    }

        /**
     * Display a page by slug
     */
    public function show($slug)
    {
        $page = $this->countryService->api('pages/' . $slug);

        if (!$page) {
            abort(404, 'Page not found');
        }

        $country = $this->countryService->getCountryData();

        return view('pages.show', compact('page', 'country'));
    }


    /**
     * Display featured social media platforms
     */
    public function featured()
    {
        $country = $this->countryService->getCountryData();
        $platforms = $this->countryService->api('social-media/featured', [
            'country_code' => $country['code'] ?? 'AU',
            'limit' => 10
        ]);

        return view('home.social-media.featured', compact('country', 'platforms'));
    }

    /**
     * Display all social media platforms for a country
     */
    public function byCountry(Request $request, $countryCode = null)
    {
        $countryCode = $countryCode ?? $this->countryService->getCode();
        $country = $this->countryService->getCountryData();
        
        $platforms = $this->countryService->api('social-media/country/' . $countryCode);

        return view('social-media.index', compact('country', 'platforms', 'countryCode'));
    }

}