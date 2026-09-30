<?php

namespace App\Http\Controllers\Home;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\CountryService;

class PricingController extends Controller
{
    public function __construct(protected CountryService $countryService) {}

    public function index()
    {
        $response = $this->countryService->api('services/pricing', [], 'GET', 3600, false);

        $services = [];
        if (is_array($response) && ($response['success'] ?? false)) {
            $services = $response['data'] ?? [];
        }

        // Group by family, in a deliberate display order
        $familyOrder = [
            'job_posting'              => 'Job Posting Packages',
            'cv_service'               => 'CV & Career Services',
            'job_seeker_subscription'  => 'Job Seeker Subscriptions',
            'free_service'             => 'Free Services',
        ];

        $grouped = [];
        foreach ($familyOrder as $key => $label) {
            $grouped[$key] = [
                'label'    => $label,
                'services' => array_values(array_filter($services, fn($s) => ($s['family'] ?? '') === $key)),
            ];
        }

        // Anything with an unknown family goes into an "Other" bucket at the end
        $known = array_keys($familyOrder);
        $other = array_values(array_filter($services, fn($s) => !in_array($s['family'] ?? '', $known, true)));
        if (!empty($other)) {
            $grouped['other'] = ['label' => 'More Services', 'services' => $other];
        }

        // Drop empty groups
        $grouped = array_filter($grouped, fn($g) => !empty($g['services']));

        return view('pricing.index', [
            'grouped'  => $grouped,
            'country'  => $this->countryService->getCountryData(),
        ]);
    }
}