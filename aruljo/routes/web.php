<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserRoleController;

require __DIR__.'/auth.php';

// Public welcome page
Route::get('/', function () {
    return view('welcome');
});

// Protected routes — only for authenticated users
Route::middleware(['auth'])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Email Verification Routes
    |--------------------------------------------------------------------------
    */
    // Notice to verify
    Route::get('/email/verify', function () {
        return view('auth.verify-email');
    })->name('verification.notice');

    // Link from email
    Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
        $request->fulfill(); // sets email_verified_at
        return redirect('/dashboard');
    })->middleware(['auth', 'signed'])->name('verification.verify');

    // Resend verification
    Route::post('/email/verification-notification', function (Request $request) {
        $request->user()->sendEmailVerificationNotification();
        return back()->with('message', 'Verification link sent!');
    })->middleware(['auth', 'throttle:6,1'])->name('verification.send');


    /*
    |--------------------------------------------------------------------------
    | Verified User Routes (only after email_verified_at is set)
    |--------------------------------------------------------------------------
    */
    Route::middleware(['verified'])->group(function () {

        // Dashboard
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // Profile
        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

        /*
        |--------------------------------------------------------------------------
        | Lead Routes (per role)
        |--------------------------------------------------------------------------
        */
        // staff & owner: all lead actions except delete
        Route::get('/leads', [LeadController::class, 'index'])->name('leads.index');
        Route::get('/leads/{lead}/edit', [LeadController::class, 'edit'])->name('leads.edit');
        Route::put('/leads/{lead}', [LeadController::class, 'update'])->name('leads.update');
        Route::get('/leads/create', [LeadController::class, 'create'])->name('leads.create');
        Route::post('/leads', [LeadController::class, 'store'])->name('leads.store');
        Route::get('/leads/{id}/audits', [LeadController::class, 'showAudits'])->name('leads.audits');

        /*
        |--------------------------------------------------------------------------
        | Admin Routes
        |--------------------------------------------------------------------------
        */
        Route::middleware(['role:admin'])->group(function () {
            Route::get('/users', [UserRoleController::class, 'index'])->name('users.list');
            Route::put('/users/{id}', [UserRoleController::class, 'role'])->name('users.update');

            // admin can do everything in leads, including delete
            Route::delete('/leads/{id}', [LeadController::class, 'destroy'])->name('leads.destroy');
        });
    });
});
