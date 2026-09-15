<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Home\{ DashboardController };
use App\Http\Controllers\Jobs\{ JobsController };


// Protected Routes - Both roles go to same dashboard, role determines content
Route::middleware(['auth.web'])->group(function () {
    // Dashboard - Single dashboard for both roles
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Role-specific routes (optional - if you need separate pages)
    Route::get('/employer/dashboard', [DashboardController::class, 'employerDashboard'])->name('employer.dashboard');
    Route::get('/seeker/dashboard', [DashboardController::class, 'seekerDashboard'])->name('seeker.dashboard');

    
});

use App\Http\Controllers\Home\{ ProfileController, CvController, AlertPreferenceController };

Route::middleware(['auth.web'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::post('/profile/update', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/avatar', [ProfileController::class, 'uploadAvatar'])->name('profile.avatar');
});

Route::middleware(['auth.web'])->group(function () {
    Route::get('/profile/cv', [CvController::class, 'edit'])->name('cv.edit');
    Route::post('/profile/cv/upload', [CvController::class, 'upload'])->name('cv.upload');
    Route::post('/cv/delete', [CvController::class, 'delete'])->name('cv.delete');
});


Route::middleware(['auth.web'])->group(function () {
    // Alert Preferences
    Route::get('/alert-preferences', [AlertPreferenceController::class, 'edit'])->name('alert.preferences');
    Route::post('/alert-preferences/update', [AlertPreferenceController::class, 'update'])->name('alert.preferences.update');

});

use App\Http\Controllers\Home\CvReviewController;

Route::middleware(['auth.web'])->prefix('cv-review')->group(function () {
    Route::get('/', [CvReviewController::class, 'index'])->name('cv-review.index');
    Route::get('/new', [CvReviewController::class, 'create'])->name('cv-review.create');
    Route::get('/my', [CvReviewController::class, 'myRequests'])->name('cv-review.my');
    Route::post('/', [CvReviewController::class, 'store'])->name('cv-review.store');
    Route::get('/{uuid}', [CvReviewController::class, 'show'])->name('cv-review.show');
    Route::post('/{uuid}/answers', [CvReviewController::class, 'submitAnswers'])->name('cv-review.answers');
    Route::post('/{uuid}/pay', [CvReviewController::class, 'pay'])->name('cv-review.pay');
    Route::post('/{uuid}/revision', [CvReviewController::class, 'requestRevision'])->name('cv-review.revision');
    Route::delete('/{uuid}', [CvReviewController::class, 'destroy'])->name('cv-review.destroy');
});