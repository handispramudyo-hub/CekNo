<?php

use App\Http\Controllers\Api\Admin\AdminDashboardController;
use App\Http\Controllers\Api\Admin\AdminNumberController;
use App\Http\Controllers\Api\Admin\AdminUserController;
use App\Http\Controllers\Api\Admin\AdminModerationController;
use App\Http\Controllers\Api\Admin\AnalyticsController;
use App\Http\Controllers\Api\Admin\AuditLogController;
use App\Http\Controllers\Api\Admin\CategoryController;
use App\Http\Controllers\Api\Admin\MlModelController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ContactContributionController;
use App\Http\Controllers\Api\NumberController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Api\TagController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

Route::post('/auth/register', [AuthController::class, 'register'])->middleware('throttle:auth.register');
Route::post('/auth/login', [AuthController::class, 'login'])->middleware('throttle:auth.login');
Route::post('/auth/forgot-password', [AuthController::class, 'forgot'])->middleware('throttle:auth.forgot');
Route::post('/auth/reset-password', [AuthController::class, 'reset'])->middleware('throttle:auth.reset');
Route::post('/auth/email/verify', [AuthController::class, 'verifyEmail']);
Route::get('/ml/health', fn () => response()->json(['status' => 'ok']));

// Search & number profile dapat diakses guest (rate-limited, tanpa identitas pribadi)
Route::get('/numbers/search', [NumberController::class, 'search'])->middleware('throttle:auth.search');
Route::get('/numbers/{phone}', [NumberController::class, 'show']);

Route::middleware(['auth:sanctum', 'user.active'])->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/me', [AuthController::class, 'me']);
    Route::post('/auth/email/resend', [AuthController::class, 'resendVerification']);

    Route::get('/numbers/{phone}/tags', [TagController::class, 'index']);
    Route::post('/numbers/{phone}/tags', [TagController::class, 'store'])->middleware('throttle:auth.tag');

    Route::post('/numbers/{phone}/reports', [ReportController::class, 'store'])->middleware('throttle:auth.report');
    Route::get('/numbers/{phone}/reports', fn (\App\Models\PhoneNumber $phone) => response()->json([
        'reports' => $phone->reports()->where('status', 'approved')->latest()->get(),
    ]));

    Route::post('/numbers/{phone}/reviews', [ReviewController::class, 'store'])->middleware('throttle:auth.review');
    Route::get('/numbers/{phone}/reviews', fn (\App\Models\PhoneNumber $phone) => response()->json([
        'reviews' => $phone->reviews()->where('status', 'approved')->latest()->get(),
    ]));;

    Route::get('/user/history', [UserController::class, 'history']);
    Route::get('/user/reports', [UserController::class, 'reports']);
    Route::get('/user/reviews', [UserController::class, 'reviews']);
    Route::get('/user/tags', [UserController::class, 'tags']);
    Route::put('/user/profile', [UserController::class, 'updateProfile'])->middleware('throttle:auth.profile');
    Route::put('/user/password', [UserController::class, 'updatePassword'])->middleware('throttle:auth.profile');

    // Kontribusi kontak
    Route::get('/user/contact-contributions', [ContactContributionController::class, 'index']);
    Route::post('/user/contact-contributions/sync', [ContactContributionController::class, 'sync'])->middleware('throttle:auth.contribution');
    Route::delete('/user/contact-contributions/{contribution}', [ContactContributionController::class, 'destroy']);

    // Admin (role via policy authorization di controller)
    Route::prefix('admin')->group(function () {
        Route::get('/dashboard', AdminDashboardController::class);
        Route::get('/users', [AdminUserController::class, 'index']);
        Route::patch('/users/{user}/status', [AdminUserController::class, 'updateStatus']);
        Route::patch('/users/{user}/role', [AdminUserController::class, 'updateRole']);

        Route::get('/numbers', [AdminNumberController::class, 'index']);
        Route::patch('/numbers/{number}/status', [AdminNumberController::class, 'updateStatus']);

        Route::get('/reports', [AdminModerationController::class, 'reports']);
        Route::post('/reports/{report}/moderate', [AdminModerationController::class, 'moderateReport']);
        Route::get('/reviews', [AdminModerationController::class, 'reviews']);
        Route::post('/reviews/{review}/moderate', [AdminModerationController::class, 'moderateReview']);
        Route::get('/tags', [AdminModerationController::class, 'tags']);
        Route::post('/tags/{phoneTag}/moderate', [AdminModerationController::class, 'moderateTag']);
        Route::get('/contributions', [AdminModerationController::class, 'contributions']);
        Route::post('/contributions/{contribution}/moderate', [AdminModerationController::class, 'moderateContribution']);

        Route::get('/categories', [CategoryController::class, 'index']);
        Route::post('/categories', [CategoryController::class, 'store']);
        Route::put('/categories/{category}', [CategoryController::class, 'update']);
        Route::delete('/categories/{category}', [CategoryController::class, 'destroy']);

        Route::get('/analytics', AnalyticsController::class);

        Route::get('/ml-models', [MlModelController::class, 'index']);
        Route::post('/ml-models/{model}/activate', [MlModelController::class, 'activate']);

        Route::get('/audit-logs', [AuditLogController::class, 'index']);
    });
});