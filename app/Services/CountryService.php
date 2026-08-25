<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class CountryService
{
    protected string $countryCode;
    protected string $countryName;
    protected string $mainAppUrl;
    protected ?string $apiKey;

    public function __construct()
    {
        $this->countryCode = config('app.country_code', 'AU');
        $this->countryName = config('app.country_name', 'Australia');
        $this->mainAppUrl = config('app.main_app_url', 'http://127.0.0.1:8000');
        $this->apiKey = config('app.main_app_api_key');
    }

    protected function getHeaders(): array
    {
        return [
            'X-Country-Code' => $this->countryCode,
            'Accept' => 'application/json',
        ];
    }

    protected function getBaseUrl(): string
    {
        return $this->mainAppUrl;
    }

    /**
     * Universal API caller.
     *
     * @param bool $unwrapData When true (default, back-compat), returns just the
     *   'data' key from the response - fine for simple endpoints like
     *   categories/locations that have no sibling data (no pagination etc).
     *   Pass false for endpoints like 'jobs' where you need the full
     *   {success, data, pagination} shape - unwrapping there was silently
     *   throwing pagination away and breaking the empty-response guard
     *   in JobsController::jobs().
     */
    public function api(string $endpoint, array $params = [], string $method = 'GET', int $cacheMinutes = 0, bool $unwrapData = true): array
    {
        // ALWAYS add API key as query parameter (works everywhere - local & production)
        if ($this->apiKey) {
            $params['api_key'] = $this->apiKey;
        }

        $url = $this->getBaseUrl() . '/api/' . ltrim($endpoint, '/');

        $cacheKey = 'api.' . $this->countryCode . '.' . md5($url . json_encode($params) . $method . ($unwrapData ? 'unwrapped' : 'full'));

        if ($method === 'GET' && $cacheMinutes > 0) {
            return Cache::remember($cacheKey, $cacheMinutes, function () use ($url, $params, $method, $unwrapData) {
                return $this->callApi($url, $params, $method, $unwrapData);
            });
        }

        return $this->callApi($url, $params, $method, $unwrapData);
    }

    protected function callApi(string $url, array $params = [], string $method = 'GET', bool $unwrapData = true): array
    {
        try {
            $response = Http::withHeaders($this->getHeaders());

            switch (strtoupper($method)) {
                case 'POST':
                    $response = $response->post($url, $params);
                    break;
                case 'PUT':
                    $response = $response->put($url, $params);
                    break;
                case 'DELETE':
                    $response = $response->delete($url, $params);
                    break;
                default:
                    $response = $response->get($url, $params);
                    break;
            }

            if ($response->successful()) {
                $data = $response->json();

                if ($unwrapData && isset($data['data'])) {
                    return $data['data'];
                }

                return $data;
            }

            // Log::warning('API call returned non-successful response', [
            //     'url' => $url,
            //     'status' => $response->status(),
            //     'body' => $response->body(),
            // ]);

            return [];
        } catch (\Exception $e) {
            Log::error('API call error: ' . $e->getMessage());
            return [];
        }
    }

    public function getCountryData(): array
    {
        $cacheKey = 'country.data.' . $this->countryCode;
        $cached = Cache::get($cacheKey);

        if ($cached) {
            return $cached;
        }

        $data = $this->api('countries/' . $this->countryCode, [], 'GET', 3600);

        if (empty($data)) {
            Log::warning('Country data empty, using .env fallback', ['country' => $this->countryCode]);
            $data = $this->getDefaultCountryData();
        }

        Cache::put($cacheKey, $data, 3600);

        return $data;
    }

    protected function getDefaultCountryData(): array
    {
        return [
            'code' => $this->countryCode,
            'name' => config('app.country_name', 'Australia'),
            'flag' => config('app.country_flag', '🇦🇺'),
            'currency' => config('app.country_currency', 'AUD'),
            'currency_symbol' => config('app.country_currency_symbol', '$'),
            'timezone' => config('app.country_timezone', 'Australia/Sydney'),
            'phone_code' => config('app.country_phone_code', '+61'),
            'region' => config('app.country_region', 'Oceania'),
            'capital' => config('app.country_capital', 'Canberra'),
            'citizens' => config('app.country_citizens', 'Australians'),
            'domain' => config('app.country_domain', 'greataustraliajobs.com'),
        ];
    }

    public function getCode(): string
    {
        return $this->countryCode;
    }

    public function getName(): string
    {
        $data = $this->getCountryData();
        return $data['name'] ?? config('app.country_name', 'Australia');
    }

    public function getAllCountries(): array
    {
        return $this->api('countries', [], 'GET', 3600);
    }

    public function getFlag(): string
    {
        $data = $this->getCountryData();
        return $data['flag'] ?? config('app.country_flag', '🇦🇺');
    }

    public function getCurrency(): string
    {
        $data = $this->getCountryData();
        return $data['currency'] ?? config('app.country_currency', 'AUD');
    }

    public function getCurrencySymbol(): string
    {
        $data = $this->getCountryData();
        return $data['currency_symbol'] ?? config('app.country_currency_symbol', '$');
    }

    public function getPhoneCode(): string
    {
        $data = $this->getCountryData();
        return $data['phone_code'] ?? config('app.country_phone_code', '+61');
    }

    public function getTimezone(): string
    {
        $data = $this->getCountryData();
        return $data['timezone'] ?? config('app.country_timezone', 'Australia/Sydney');
    }

    public function getRegion(): string
    {
        $data = $this->getCountryData();
        return $data['region'] ?? config('app.country_region', 'Oceania');
    }

    public function getCapital(): string
    {
        $data = $this->getCountryData();
        return $data['capital'] ?? config('app.country_capital', 'Canberra');
    }

    public function getCitizens(): string
    {
        $data = $this->getCountryData();
        return $data['citizens'] ?? config('app.country_citizens', 'Australians');
    }

    public function getDomain(): string
    {
        $data = $this->getCountryData();
        return $data['domain'] ?? config('app.country_domain', 'greataustraliajobs.com');
    }

    public function clearCache(?string $key = null): void
    {
        if ($key) {
            Cache::forget($key);
        } else {
            Cache::flush();
        }
    }
}