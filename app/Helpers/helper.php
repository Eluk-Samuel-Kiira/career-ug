<?php

use App\Services\CountryService;

if (!function_exists('country_service')) {
    function country_service(): CountryService
    {
        return app(CountryService::class);
    }
}

if (!function_exists('country_code')) {
    function country_code(): string
    {
        return config('app.country_code', 'AU');
    }
}

if (!function_exists('country_name')) {
    function country_name(): string
    {
        return config('app.country_name', 'Australia');
    }
}

if (!function_exists('country_citizens')) {
    function country_citizens(): string
    {
        return config('app.country_citizens', 'Australians');
    }
}

if (!function_exists('app_name')) {
    function app_name(): string
    {
        return config('app.name', 'JobMatch');
    }
}

if (!function_exists('country_flag')) {
    function country_flag(): string
    {
        return country_service()->getFlag();
    }
}

if (!function_exists('country_currency')) {
    function country_currency(): string
    {
        return country_service()->getCurrency();
    }
}

if (!function_exists('country_currency_symbol')) {
    function country_currency_symbol(): string
    {
        return country_service()->getCurrencySymbol();
    }
}

if (!function_exists('country_phone_code')) {
    function country_phone_code(): string
    {
        return country_service()->getPhoneCode();
    }
}

if (!function_exists('country_timezone')) {
    function country_timezone(): string
    {
        return country_service()->getTimezone();
    }
}

if (!function_exists('country_region')) {
    function country_region(): string
    {
        return country_service()->getRegion();
    }
}

if (!function_exists('country_capital')) {
    function country_capital(): string
    {
        return country_service()->getCapital();
    }
}

if (!function_exists('all_countries')) {
    function all_countries(): array
    {
        return country_service()->getAllCountries();
    }
}

// Logo and Favicon Helpers (Local)
if (!function_exists('country_logo')) {
    function country_logo(): string
    {
        $countryCode = country_name();
        $logoPath = "logos/{$countryCode}.png";
        
        // Check if country-specific logo exists
        if (file_exists(public_path($logoPath))) {
            return asset($logoPath);
        }
        
        // Fallback to default logo
        return asset('assets/media/logos/australia.png');
    }
}

if (!function_exists('country_favicon')) {
    function country_favicon(): string
    {
        $countryCode = country_name();
        $faviconPath = "logos/{$countryCode}.ico";
        
        // Check if country-specific favicon exists
        if (file_exists(public_path($faviconPath))) {
            return asset($faviconPath);
        }
        
        // Check for png favicon
        $faviconPngPath = "assets/media/logos/{$countryCode}.png";
        if (file_exists(public_path($faviconPngPath))) {
            return asset($faviconPngPath);
        }
        
        // Fallback to default favicon
        return asset('assets/media/logos/australia.ico');
    }
}

if (!function_exists('country_logo_path')) {
    function country_logo_path(): string
    {
        return public_path("logos/" . country_code() . ".png");
    }
}

if (!function_exists('country_favicon_path')) {
    function country_favicon_path(): string
    {
        return public_path("logos/" . country_code() . ".ico");
    }
}



