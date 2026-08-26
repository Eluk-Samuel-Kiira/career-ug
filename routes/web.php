<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Home\{ HomeController, ArtisanCommandController };

use App\Http\Controllers\Blog\BlogController;
use App\Http\Controllers\Auth\{ AuthController };

// Landing page
Route::get('/', [HomeController::class, 'index'])->name('home');

// Artisan Command Runner - Frontend
Route::get('protected/artisan', [ArtisanCommandController::class, 'index'])->name('frontend.artisan.index');
Route::post('/artisan/run', [ArtisanCommandController::class, 'run'])->name('frontend.artisan.run');

use App\Http\Controllers\Jobs\{ JobsController, CompaniesController, CategoryController, LocationController };
// Jobs listing
Route::get('/jobs', [JobsController::class, 'jobs'])->name('jobs.index');
// Job detail
Route::get('/job/{id}', [JobsController::class, 'show'])->name('jobs.show');
// Job application
Route::post('/jobs/{id}/track-application', [JobsController::class, 'trackApplication'])
    ->name('jobs.track-application');


Route::get('/companies', [CompaniesController::class, 'companies'])->name('companies.index');
// Company detail
Route::get('/jobs/company/{id}', [CompaniesController::class, 'show'])->name('companies.show');
Route::get('/jobs/category/{id}', [CategoryController::class, 'show'])->name('categories.show');

Route::get('/jobs/location/{slug}', [LocationController::class, 'show'])->name('locations.show');


Route::get('/pages/{slug}', [HomeController::class, 'show'])->name('pages.show');

// Direct routes for common pages (optional - redirects to slug-based routes)
Route::get('pages/about', function () {
    return redirect()->route('pages.show', 'about');
})->name('about');

Route::get('pages/contact', function () {
    return redirect()->route('pages.show', 'contact');
})->name('contact');

Route::get('pages/privacy-policy', function () {
    return redirect()->route('pages.show', 'privacy-policy');
})->name('privacy');

Route::get('pages/terms-conditions', function () {
    return redirect()->route('pages.show', 'terms-conditions');
})->name('terms');


Route::get('/social-media', [HomeController::class, 'byCountry'])
    ->name('social-media.index');
Route::get('/social-media/featured', [HomeController::class, 'featured'])
    ->name('social-media.featured');
Route::get('/social-media/{countryCode}', [HomeController::class, 'byCountry'])
    ->name('social-media.by-country');





// Blog - Frontend
Route::prefix('blog')->group(function () {
    Route::get('/', [BlogController::class, 'index'])->name('blog.index');
    Route::get('/create', [BlogController::class, 'create'])->name('blog.create');
    Route::get('/edit/{slug}', [BlogController::class, 'edit'])->name('blog.edit');
    Route::get('/{slug}', [BlogController::class, 'show'])->name('blog.show');
});



// Authentication pages
Route::get('/register/employer', function () {
    return view('auth.register-employer');
})->name('register.employer');

Route::get('/login', function () {
    return view('auth.login-register');
})->name('login');

Route::get('/register', function () {
    return view('auth.login-register');
})->name('register');
Route::post('/register', [AuthController::class, 'register'])->name('register');


// Login - Send magic link
Route::post('/login/magic-link', [AuthController::class, 'sendMagicLink'])->name('login.magic-link');
// Login - Verify magic link (the link the user clicks in email)
Route::get('/login/magic-link/{token}', [AuthController::class, 'verifyMagicLink'])->name('login.magic-link.verify');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');


Route::get('/contact', function () {
    return view('pages.contact');
})->name('contact');




// Blog routes - public facing
Route::get('/blog', [BlogController::class, 'index'])->name('blog.index');
Route::get('/blog/{slug}', [BlogController::class, 'show'])->name('blog.show');



Route::get('/sitemaps/{country}/{filename}', function ($country, $filename) {
    $country = strtolower($country);
    // \Log::info($country);
    $mainAppUrl = config('app.main_app_url');
    $sitemapUrl = $mainAppUrl . "/sitemaps/{$country}/{$filename}";
    
    try {
        $response = Http::timeout(30)->get($sitemapUrl);
        
        if (!$response->successful()) {
            abort(404);
        }
        
        return response($response->body(), 200)
            ->header('Content-Type', 'application/xml');
    } catch (\Exception $e) {
        abort(404);
    }
})->where('filename', '.*\.xml$');


require __DIR__.'/auth.php';