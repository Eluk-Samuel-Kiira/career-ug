<?php

namespace App\Http\Controllers\Blog;

use App\Http\Controllers\Controller;
use App\Services\CountryService;
use Illuminate\Http\Request;

class BlogController extends Controller
{
    protected CountryService $countryService;

    public function __construct(CountryService $countryService)
    {
        $this->countryService = $countryService;
    }

    /**
     * Display blog listing page
     */
    public function index(Request $request)
    {
        $country = $this->countryService->getCountryData();
        $countryCode = $country['code'] ?? 'UG';
        
        // Get filters from request
        $filters = [
            'country_code' => $countryCode,
            'search' => $request->get('search'),
            'category' => $request->get('category'),
            'page' => $request->get('page', 1),
            'per_page' => $request->get('per_page', 12),
            'is_published' => true,
            'is_active' => true,
        ];

        // Remove null filters
        $filters = array_filter($filters, function($value) {
            return !is_null($value) && $value !== '';
        });

        // Fetch blogs from API
        $blogsResponse = $this->countryService->api('blogs', $filters, 'GET', 300, unwrapData: false);

        // Get featured blogs
        $featuredResponse = $this->countryService->api('blogs', [
            'country_code' => $countryCode,
            'featured' => true,
            'per_page' => 3,
            'is_published' => true,
            'is_active' => true,
        ], 'GET', 300, unwrapData: false);

        $featuredBlogs = $featuredResponse['data'] ?? [];

        if (empty($blogsResponse) || !isset($blogsResponse['data'])) {
            $blogs = ['data' => [], 'pagination' => ['current_page' => 1, 'last_page' => 1, 'total' => 0]];
        } else {
            $blogs = [
                'data' => $blogsResponse['data'],
                'pagination' => $blogsResponse['pagination'] ?? [
                    'current_page' => 1,
                    'last_page' => 1,
                    'total' => count($blogsResponse['data'])
                ],
            ];
        }

        // Get categories for filter
        $categories = $this->countryService->api('blogs/categories', ['country_code' => $countryCode]);
        $categories = $categories['data'] ?? $categories ?? [];

        $totalBlogs = $blogs['pagination']['total'] ?? 0;

        return view('blog.index', compact('country', 'blogs', 'categories', 'filters', 'featuredBlogs', 'totalBlogs'));
    }

    /**
     * Display a single blog post
     */
    public function show(Request $request, $slug)
    {
        $country = $this->countryService->getCountryData();
        $countryCode = $country['code'] ?? 'UG';
        
        // Fetch blog from API
        $blog = $this->countryService->api('blogs/' . $slug, ['country_code' => $countryCode]);
        
        // Debug: Check what's in the cover_image
        \Log::info('Blog cover_image:', ['cover_image' => $blog['cover_image'] ?? 'null']);
        
        if (!$blog || isset($blog['error'])) {
            abort(404, 'Blog post not found');
        }

        // Increment view count
        if (isset($blog['id'])) {
            $this->countryService->api('blogs/' . $blog['id'] . '/view', [], 'POST');
        }

        // Get related blogs
        $relatedResponse = $this->countryService->api('blogs', [
            'country_code' => $countryCode,
            'category' => $blog['category'] ?? null,
            'per_page' => 4,
            'exclude' => $blog['id'] ?? null,
            'is_published' => true,
            'is_active' => true,
        ], 'GET', 300, unwrapData: false);

        $relatedBlogs = $relatedResponse['data'] ?? [];

        return view('blog.show', compact('country', 'blog', 'relatedBlogs'));
    }

}