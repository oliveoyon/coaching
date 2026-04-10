<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\BatchController;
use App\Http\Controllers\FeeHeadController;
use App\Http\Controllers\FeeStructureController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\StudentEnrollmentController;
use App\Http\Controllers\StudentFeeOverrideController;
use App\Http\Controllers\TenantBillingConfigController;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', DashboardController::class)
    ->middleware(['auth', 'verified', 'tenant'])
    ->name('dashboard');

Route::middleware(['auth', 'tenant'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/billing-settings', [TenantBillingConfigController::class, 'edit'])->name('billing-settings.edit');
    Route::put('/billing-settings', [TenantBillingConfigController::class, 'update'])->name('billing-settings.update');
    Route::resource('fee-heads', FeeHeadController::class)->except('show');
    Route::resource('fee-structures', FeeStructureController::class)->except('show');
    Route::resource('student-fee-overrides', StudentFeeOverrideController::class)->except('show');
    Route::resource('teachers', TeacherController::class)->except('show');
    Route::resource('students', StudentController::class)->except('show');
    Route::resource('enrollments', StudentEnrollmentController::class)->except('show');
    Route::resource('batches', BatchController::class)->except('show');
});

require __DIR__.'/auth.php';
