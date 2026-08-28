<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Home\{ DashboardController };


// Protected Routes - Both roles go to same dashboard, role determines content
Route::middleware(['auth.web'])->group(function () {
    // Dashboard - Single dashboard for both roles
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Role-specific routes (optional - if you need separate pages)
    Route::get('/employer/dashboard', [DashboardController::class, 'employerDashboard'])->name('employer.dashboard');
    Route::get('/seeker/dashboard', [DashboardController::class, 'seekerDashboard'])->name('seeker.dashboard');

    
});

use App\Http\Controllers\Home\{ ProfileController, CvController };

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