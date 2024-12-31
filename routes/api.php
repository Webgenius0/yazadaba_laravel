<?php

use App\Http\Controllers\API\Teacher\CourseController;
use App\Http\Controllers\API\Teacher\CourseModuleController;
use App\Http\Controllers\API\Teacher\CurriculumController;
use App\Http\Controllers\API\Teacher\HomeController;
use App\Http\Controllers\API\Teacher\ResourceValueController;
use App\Http\Controllers\API\Teacher\ReviewController;
use App\Http\Controllers\API\Teacher\TeacherMentorController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\Auth\UserController;
use App\Http\Controllers\API\Auth\LoginController;
use App\Http\Controllers\API\Auth\LogoutController;
use App\Http\Controllers\API\Auth\RegisterController;
use App\Http\Controllers\API\Auth\SocialLoginController;
use App\Http\Controllers\API\Auth\ResetPasswordController;

Route::group(['middleware' => 'guest:api'], static function () {
    //register
    Route::post('register', [RegisterController::class, 'register']);
    Route::post('/verify-email', [RegisterController::class, 'VerifyEmail']);
    Route::post('/resend-otp', [RegisterController::class, 'ResendOtp']);
    //login
    Route::post('login', [LoginController::class, 'login']);
    //forgot password
    Route::post('/forget-password', [ResetPasswordController::class, 'forgotPassword']);
    Route::post('/verify-otp', [ResetPasswordController::class, 'VerifyOTP']);
    Route::post('/reset-password', [ResetPasswordController::class, 'ResetPassword']);
    //social login
    Route::post('/social-login', [SocialLoginController::class, 'SocialLogin']);
});

Route::group(['middleware' => 'auth:api'], static function () {
    Route::get('/refresh-token', [LoginController::class, 'refreshToken']);
    Route::post('/logout', [LogoutController::class, 'logout']);

    //Teacher Profile management

    Route::get('/teacher/profile', [UserController::class, 'TeacherProfile']);
    Route::post('/teacher/upload-avatar', [UserController::class, 'TeacherUploadAvatar']);
    Route::post('/teacher/update-profile', [UserController::class, 'TeacherUpdateProfile']);
    Route::delete('/teacher/delete-profile/{id}', [UserController::class, 'TeacherDeleteProfile']);


    //course related route
    Route::controller(CourseController::class)->prefix('course')->group(function () {
        Route::get('/', 'view');
        Route::post('/create', 'create');
        Route::post('/update/{id}', 'update');
        Route::post('/delete/{id}', 'delete');
        Route::get('/get/categories', 'getCategories');
        Route::get('/get/grade-level', 'getGradeLevel');
    });

    Route::controller(CourseModuleController::class)->prefix('course-module')->group(function () {
        Route::get('/', 'view');
        Route::post('/create', 'create');
        Route::post('/update/{moduleId}', 'update');
        Route::delete('/delete/{id}', 'delete');
    });
    //Teacher mentor all route
    Route::controller(TeacherMentorController::class)->prefix('teacher-mentor')->group(function () {
        Route::get('/', 'index');
        Route::post('/create', 'create');
        Route::post('/update/{moduleId}', 'update');
        Route::delete('/delete/{id}', 'delete');
    });

    //Teacher mentor all route
    Route::controller(CurriculumController::class)->prefix('course-curriculum')->group(function () {
        Route::get('/details/{curriculum}', 'details');
    });

    //Teacher review
    Route::controller(ReviewController::class)->prefix('review')->group(function () {
        Route::get('/details', 'index');
        Route::post('/create/{id}', 'submitReview');
    });

    //Teacher Home Api
    Route::controller(HomeController::class)->prefix('home')->group(function () {
        Route::get('/', 'index');
        Route::get('/filter-category', 'filterCategory');
        Route::get('/search-course', 'searchByCourse');
        Route::get('/sales', 'sales');
    });


    Route::controller(ResourceValueController::class)->prefix('home')->group(function () {
        Route::get('/resource/performance/metrics', 'RevenueBreakdown');
    });


    //enroll student list
    Route::controller(\App\Http\Controllers\API\Teacher\CertificateController::class)->prefix('student')->group
    (function () {
        Route::get('/list/{course_id}', 'index');
        Route::post('/certificate', 'store');
    });
    //Student home all route
    Route::controller(\App\Http\Controllers\API\Student\HomeController::class)->prefix('home/student')->group(function () {
        Route::get('/', 'index');
    });
    Route::controller(\App\Http\Controllers\API\Student\CurriculumController::class)->prefix('student/course-curriculum')->group(function () {
        Route::get('/details/{curriculum}', 'details');
    });
    Route::controller(\App\Http\Controllers\API\Student\MyResourceController::class)->prefix('my-resources')->group
    (function () {
        Route::get('/', 'index');
    });
    //Student  mentor all route
    Route::controller(\App\Http\Controllers\API\Student\MentorController::class)->prefix('student/teacher-mentor')->group(function () {
        Route::get('/{user_id}', 'index');
        Route::post('/create', 'create');
        Route::post('/update/{moduleId}', 'update');
        Route::delete('/delete/{id}', 'delete');
    });

    //Student Profile management

    Route::get('/student/profile', [UserController::class, 'StudentProfile']);
    Route::post('/student/upload-avatar', [UserController::class, 'StudentUploadAvatar']);
    Route::post('/student/update-profile', [UserController::class, 'StudentUpdateProfile']);
    Route::delete('/student/delete-profile/{id}', [UserController::class, 'StudentDeleteProfile']);

    Route::post('/enroll', [\App\Http\Controllers\API\Student\EnrollController::class, 'enroll']);
    Route::post('/is-complete', [\App\Http\Controllers\API\Student\IsCompleteController::class, 'isComplete']);

});









