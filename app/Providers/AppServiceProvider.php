<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Cache;
use App\Services\CountryService;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // ─────────────────────────────────────────────────────────
        // Global share: filter dropdowns (categories, industries,
        // job types, locations, experience/education levels,
        // salary ranges, countries)
        // ─────────────────────────────────────────────────────────
        View::composer('*', function ($view) {
            $countryService = app(CountryService::class);
            $countryCode    = $countryService->getCode();

            $cacheKey = "filters.dropdowns.{$countryCode}";

            $filters = Cache::remember($cacheKey, now()->addHour(), function () use ($countryService) {
                $data = $countryService->api('filters/dropdowns', [], 'GET', 3600);

                // If the API failed, return empty arrays so blade doesn't explode
                if (!is_array($data) || ($data['success'] ?? true) === false) {
                    return [
                        'categories'        => [],
                        'industries'        => [],
                        'job_types'         => [],
                        'locations'         => [],
                        'experience_levels' => [],
                        'education_levels'  => [],
                        'salary_ranges'     => [],
                        'countries'         => [],
                    ];
                }

                return $data;
            });

            $view->with('filters', $filters);
        });

        // ─────────────────────────────────────────────────────────
        // Header (nav categories & locations) — existing behaviour
        // ─────────────────────────────────────────────────────────
        View::composer('layouts.header', function ($view) {
            $countryService = app(CountryService::class);

            $categories = $countryService->api('all_categories', [], 'GET', 3600);
            $locations  = $countryService->api('locations', [], 'GET', 3600);

            $view->with('navCategories', is_array($categories) ? $categories : []);
            $view->with('navLocations',  is_array($locations)  ? $locations  : []);
        });

        // ─────────────────────────────────────────────────────────
        // Footer pages
        // ─────────────────────────────────────────────────────────
        View::composer('layouts.footer', function ($view) {
            $countryService = app(CountryService::class);

            $pages = $countryService->api('pages', [], 'GET', 3600);

            $footerPages = [];
            if (is_array($pages) && !empty($pages)) {
                usort($pages, fn($a, $b) => ($a['sort_order'] ?? 0) <=> ($b['sort_order'] ?? 0));
                $footerPages = $pages;
            }

            $view->with('footerPages', $footerPages);
        });
    }
}