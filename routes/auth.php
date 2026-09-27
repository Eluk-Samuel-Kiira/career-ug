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



use App\Http\Controllers\Employer\{ AtsController, EmployerProfileController, ComplianceController, JobSubmissionController };

Route::middleware(['auth.web'])->prefix('employer')->name('employer.')->group(function () {
    Route::get('/profile',           [EmployerProfileController::class, 'index'])->name('profile.index');
    Route::post('/profile',          [EmployerProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/logo',     [EmployerProfileController::class, 'uploadLogo'])->name('profile.logo.upload');
    Route::delete('/profile/logo',   [EmployerProfileController::class, 'deleteLogo'])->name('profile.logo.delete');
});



Route::middleware(['auth.web'])->prefix('employer')->name('employer.')->group(function () {

    Route::get('/compliance',              [ComplianceController::class, 'index'])->name('compliance.index');
    Route::post('/compliance/{type}',      [ComplianceController::class, 'upload'])->name('compliance.upload');
    Route::delete('/compliance/{type}',    [ComplianceController::class, 'destroy'])->name('compliance.destroy');
    Route::post('/compliance/submit',      [ComplianceController::class, 'submit'])->name('compliance.submit');
});



Route::middleware(['auth.web'])->prefix('employer')->name('employer.')->group(function () {

    // Job Submissions
    Route::get('/jobs',                 [JobSubmissionController::class, 'index'])->name('jobs.index');
    Route::get('/jobs/new',             [JobSubmissionController::class, 'create'])->name('jobs.create');
    Route::post('/jobs',                [JobSubmissionController::class, 'store'])->name('jobs.store');
    Route::get('/jobs/{uuid}',          [JobSubmissionController::class, 'show'])->name('jobs.show');
    Route::post('/jobs/{uuid}/payment', [JobSubmissionController::class, 'recordPayment'])->name('jobs.payment');
    Route::post('/jobs/{uuid}/cancel',  [JobSubmissionController::class, 'cancel'])->name('jobs.cancel');
    Route::delete('/jobs/{uuid}',       [JobSubmissionController::class, 'destroy'])->name('jobs.destroy');
});


Route::middleware(['auth.web'])->prefix('employer')->name('employer.')->group(function () {

    Route::post('/jobs/{slug}/applicants/screen', [AtsController::class, 'screen'])->name('ats.screen');
    Route::get('/jobs/ats/batches/{uuid}',        [AtsController::class, 'batchStatus'])->name('ats.batch-status');
    Route::get('/jobs/{slug}/applicants',         [AtsController::class, 'index'])->name('ats.index');
    Route::get('/jobs/{slug}/applicants/data',    [AtsController::class, 'data'])->name('ats.data');
    Route::get('/jobs/{slug}/applicants/export',  [AtsController::class, 'export'])->name('ats.export');
    Route::post('/jobs/{slug}/applicants/bulk',   [AtsController::class, 'bulk'])->name('ats.bulk');
    Route::get('/jobs/{slug}/applicants/{id}',    [AtsController::class, 'show'])->name('ats.show');
    Route::put('/jobs/{slug}/applicants/{id}',    [AtsController::class, 'update'])->name('ats.update');
    Route::get('/jobs/{slug}/applicants/{id}/cv', [AtsController::class, 'downloadCv'])->name('ats.download-cv');
});


use App\Http\Controllers\Employer\AnalyticsController;

Route::middleware(['auth.web'])->prefix('employer')->name('employer.')->group(function () {

    Route::get('/analytics',      [AnalyticsController::class, 'index'])->name('analytics.index');
    Route::get('/analytics/data', [AnalyticsController::class, 'data'])->name('analytics.data');
});


Route::middleware(['auth.web'])->prefix('employer')->name('employer.')->group(function () {

    Route::prefix('cv-filter')->name('cv-filter.')->group(function () {
        Route::get('/',           [\App\Http\Controllers\Employer\CvFilterController::class, 'index'])->name('index');
        Route::get('/data',       [\App\Http\Controllers\Employer\CvFilterController::class, 'data'])->name('data');
        Route::get('/{id}',       [\App\Http\Controllers\Employer\CvFilterController::class, 'show'])->name('show');
        Route::get('/{id}/cv',    [\App\Http\Controllers\Employer\CvFilterController::class, 'viewCv'])->name('cv');
    });
});


Route::middleware(['auth.web'])->prefix('letters')->name('letters.')->group(function () {
    Route::get('/',               [\App\Http\Controllers\Seeker\LetterController::class, 'index'])->name('index');
    Route::get('/create',         [\App\Http\Controllers\Seeker\LetterController::class, 'create'])->name('create');
    Route::post('/',              [\App\Http\Controllers\Seeker\LetterController::class, 'store'])->name('store');
    Route::get('/search-jobs',    [\App\Http\Controllers\Seeker\LetterController::class, 'searchJobs'])->name('search-jobs');
    Route::get('/{uuid}',         [\App\Http\Controllers\Seeker\LetterController::class, 'show'])->name('show');
    Route::post('/{uuid}/pay',    [\App\Http\Controllers\Seeker\LetterController::class, 'pay'])->name('pay');
    Route::get('/{uuid}/download',[\App\Http\Controllers\Seeker\LetterController::class, 'download'])->name('download');
    Route::delete('/{uuid}',      [\App\Http\Controllers\Seeker\LetterController::class, 'destroy'])->name('destroy');
});