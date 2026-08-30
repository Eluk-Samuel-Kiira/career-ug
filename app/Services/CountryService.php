<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

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
        $headers = [
            'X-Country-Code' => $this->countryCode,
            'Accept' => 'application/json',
        ];

        // Add Bearer token if available in session
        $accessToken = Session::get('access_token');
        if ($accessToken) {
            $headers['Authorization'] = 'Bearer ' . $accessToken;
        }

        return $headers;
    }

    protected function getBaseUrl(): string
    {
        return $this->mainAppUrl;
    }

    /**
     * Universal API caller - handles both plain JSON/form requests and
     * multipart file uploads through the same path. Pass $files to attach
     * one or more Illuminate\Http\UploadedFile instances (or raw streams);
     * when $files is non-empty the request is automatically sent as
     * multipart/form-data instead of the normal body, so callers don't
     * need to pick a different method for uploads.
     */
    public function api(string $endpoint, array $params = [], string $method = 'GET', int $cacheMinutes = 0, bool $unwrapData = true, array $files = []): array
    {
        // ALWAYS add API key as query parameter (this ensures it works with the middleware)
        if ($this->apiKey) {
            $params['api_key'] = $this->apiKey;
        }

        $url = $this->getBaseUrl() . '/api/' . ltrim($endpoint, '/');

        $cacheKey = 'api.' . $this->countryCode . '.' . md5($url . json_encode($params) . $method . ($unwrapData ? 'unwrapped' : 'full'));

        // Never cache file uploads, authenticated requests, or non-GET calls
        $hasToken = Session::has('access_token');
        $shouldCache = $method === 'GET' && $cacheMinutes > 0 && !$hasToken && empty($files);

        if ($shouldCache) {
            return Cache::remember($cacheKey, $cacheMinutes, function () use ($url, $params, $method, $unwrapData, $files) {
                return $this->callApi($url, $params, $method, $unwrapData, $files);
            });
        }

        return $this->callApi($url, $params, $method, $unwrapData, $files);
    }

    protected function callApi(string $url, array $params = [], string $method = 'GET', bool $unwrapData = true, array $files = []): array
    {
        try {
            $headers = $this->getHeaders();
            
            // ⚠️ FIX: Set timeout based on whether it's a CV upload or regular request
            $timeout = 30; // default timeout
            
            // Check if this is a CV upload endpoint (takes longer to process)
            if (str_contains($url, '/auth/user/cv')) {
                $timeout = 180; // 3 minutes for CV processing
            } elseif (str_contains($url, '/auth/user/avatar')) {
                $timeout = 60; // 1 minute for avatar upload
            }
            
            $http = Http::timeout($timeout)->withHeaders($headers);

            // Attach any files - this switches the underlying Guzzle request
            // to multipart/form-data automatically, so the same method call
            // handles both "normal" API calls and file uploads.
            foreach ($files as $fieldName => $file) {
                if ($file instanceof \Illuminate\Http\UploadedFile) {
                    $http = $http->attach(
                        $fieldName,
                        fopen($file->getRealPath(), 'r'),
                        $file->getClientOriginalName()
                    );
                } elseif (is_resource($file)) {
                    $http = $http->attach($fieldName, $file);
                }
            }

            switch (strtoupper($method)) {
                case 'POST':
                    $response = $http->post($url, $params);
                    break;
                case 'PUT':
                    $response = $http->put($url, $params);
                    break;
                case 'DELETE':
                    $response = $http->delete($url, $params);
                    break;
                default:
                    $response = $http->get($url, $params);
                    break;
            }

            if ($response->successful()) {
                $data = $response->json();

                if ($unwrapData && isset($data['data'])) {
                    return $data['data'];
                }

                return $data;
            }

            Log::warning('⚠️ API call returned non-successful response', [
                'url' => $url,
                'status' => $response->status(),
                'body' => $response->body(),
                'had_files' => !empty($files),
            ]);

            return [
                'success' => false,
                'message' => $response->json()['message'] ?? 'API request failed',
                'status' => $response->status(),
            ];

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error('⏱️ API connection timeout: ' . $e->getMessage(), [
                'url' => $url,
                'method' => $method,
                'had_files' => !empty($files),
            ]);
            return [
                'success' => false,
                'message' => 'The request is taking too long. Please try again.',
                'status' => 408,
            ];
        } catch (\Exception $e) {
            Log::error('❌ API call error: ' . $e->getMessage(), [
                'url' => $url,
                'method' => $method,
                'had_files' => !empty($files),
            ]);
            return [
                'success' => false,
                'message' => 'Connection error: ' . $e->getMessage(),
            ];
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