<?php

use App\Http\Controllers\API\Auth\SocialLoginController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Web\Backend\CategoryController;
use App\Http\Controllers\Web\Backend\CoursesController;
use App\Http\Controllers\Web\Backend\GradeLevelController;
use App\Http\Controllers\Web\Backend\SystemSettingController;
use App\Http\Controllers\Web\Backend\UserController;
use App\Http\Controllers\Web\Backend\WithdrawCompleteController;
use App\Http\Controllers\Web\Backend\WithdrawRejectController;
use App\Http\Controllers\Web\Backend\WithdrawRequestController;
use App\Http\Controllers\Web\Backend\TermsAndConditionController;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;


Route::get('/', static function () {
    if (Auth::check()) {
        return redirect()->route('dashboard');
    }
    return view('auth.login');
});

Route::get('/dashboard', static function () {
    return view('backend.layout.dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Category all route start

Route::controller(CategoryController::class)->prefix('admin/category')->name('admin.category.')->group(function () {
    Route::get('/', 'index')->name('index');
    Route::get('/create', 'create')->name('create');
    Route::post('/store', 'store')->name('store');
    Route::get('/{category}/edit', 'edit')->name('edit');
    Route::put('/{category}', 'update')->name('update');
    Route::delete('/{id}', 'destroy')->name('destroy');
    Route::get('status/{id}', 'status')->name('status');
})->middleware(['auth']);
// Category all route end

// Grade Level all route start

Route::controller(GradeLevelController::class)->prefix('admin/grade-level')->name('admin.grade-level.')->group(function () {
    Route::get('/', 'index')->name('index');
    Route::get('/create', 'create')->name('create');
    Route::post('/store', 'store')->name('store');
    Route::get('/{gradeLevel}/edit', 'edit')->name('edit');
    Route::put('/{gradeLevel}', 'update')->name('update');
    Route::delete('/{course}', 'destroy')->name('destroy');
    Route::get('status/{course}', 'status')->name('status');
})->middleware(['auth']);
// Grade Level all route end

// Course all route start

Route::controller(UserController::class)->prefix('admin/user')->name('admin.user.')->group(function () {
    Route::get('/', 'index')->name('index');
    Route::delete('/{id}', 'destroy')->name('destroy');

})->middleware(['auth']);
// Course all route end


// Course all route start

Route::controller(CoursesController::class)->prefix('admin/course')->name('admin.course.')->group(function () {
    Route::get('/', 'index')->name('index');
    Route::delete('/{course}', 'destroy')->name('destroy');
    Route::get('status/{course}', 'status')->name('status');
    Route::get('/{course}', 'show')->name('show');
})->middleware(['auth']);
// Course all route end

// Withdraw Request all route start

Route::controller(WithdrawRequestController::class)->prefix('admin/withdraw/request')->name('admin.withdraw.request.')->group(function () {
    Route::get('/', 'index')->name('index');
    Route::delete('/{id}', 'destroy')->name('destroy');
    Route::post('status/{id}', 'status')->name('status');
    Route::get('/{id}', 'show')->name('show');
})->middleware(['auth']);
Route::post('/withdraw-requests/{id}/{userId}/reject', [WithdrawRequestController::class, 'submitRejectionReason'])->middleware(['auth']);

// Withdraw Request all route end


Route::controller(WithdrawCompleteController::class)->prefix('admin/withdraw/complete')->name('admin.withdraw.complete.')->group(function () {
    Route::get('/', 'index')->name('index');
    Route::delete('/{id}', 'destroy')->name('destroy');
    Route::get('status/{id}', 'status')->name('status');
    Route::get('/{id}', 'show')->name('show');
})->middleware(['auth']);
// Withdraw Request all route end
// Withdraw Request all route start

Route::controller(WithdrawRejectController::class)->prefix('admin/withdraw/reject')->name('admin.withdraw.reject.')->group(function () {
    Route::get('/', 'index')->name('index');
    Route::delete('/{id}', 'destroy')->name('destroy');
    Route::get('status/{id}', 'status')->name('status');
    Route::get('/{id}', 'show')->name('show');
    Route::post('/{id}', 'store')->name('store');
})->middleware(['auth']);

//Terms && condition
Route::controller(TermsAndConditionController::class)->prefix('admin/terms-and-condition')->name('admin.terms-and-condition.')->group(function () {
    Route::get('/', 'termsandCondition')->name('index');
    Route::post('/terms-and-condition', 'update')->name('update');
    Route::get('/apps/terms-and-condition', 'appTermsAndCondition');

    Route::get('apps/privacy-policy', 'appPrivacyPolicy');
    Route::get('/privacy-policy', 'privacyPolicy')->name('privacyPolicy');
    Route::post('/privacy-policy/update', 'updatePrivecyPolicy')->name('updatePrivecyPolicy');
})->middleware(['auth']);
// Withdraw Request all route end

//System  settings all route

Route::controller(SystemSettingController::class)->group(function () {
    Route::get('/system-setting', 'index')->name('system.setting');
    Route::post('/system-setting', 'update')->name('system.update');
    Route::get('/system/mail', 'mailSetting')->name('system.mail.index');
    Route::post('/system/mail', 'mailSettingUpdate')->name('system.mail.update');
    Route::get('/system/profile', 'profileIndex')->name('profile.setting');
    Route::post('/profile', 'profileUpdate')->name('profile.update');
    Route::post('password', 'passwordUpdate')->name('password.update');
})->middleware(['auth']);

Route::get('social-login/{provider}', [SocialLoginController::class, 'RedirectToProvider'])->name('social.login');
Route::get('social-login/callback/{provider}', [SocialLoginController::class, 'HandleProviderCallback']);
Route::get('course/publication/request', [\App\Http\Controllers\Web\Backend\CoursePublicationController::class, 'index'])->name('course.publication.request')->middleware(['auth']);
Route::get('course/publish/request/{id}', [\App\Http\Controllers\Web\Backend\CoursePublicationController::class, 'destroy'])->name('course.publish.request.destroy')->middleware(['auth']);

Route::controller(TermsAndConditionController::class)->group(function () {
    Route::get('/apps/terms-and-condition', 'appTermsAndCondition');
    Route::get('/apps/privacy-policy', 'appPrivacyPolicy');
});
require __DIR__ . '/auth.php';
