<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserRoleController;
use App\Http\Controllers\DistanceController;
use App\Http\Controllers\Product\ProductController;
use App\Http\Controllers\Product\UnitController;
use App\Http\Controllers\Product\HsncodeController;
use App\Http\Controllers\Product\ProductTemplateController;
use App\Http\Controllers\Transport\TpOfficeController;
use App\Http\Controllers\Transport\TpDistrictRateController;
use App\Http\Controllers\Quotation\QuotationController;

require __DIR__.'/auth.php';

/*
|--------------------------------------------------------------------------
| Public / Landing Pages
|--------------------------------------------------------------------------
*/

// Aruljo webpage
Route::get('/', function () {
    return response()->file(base_path('../public_html/Index.html'));
});

// Public welcome page
Route::get('/welcome', function () {
    return view('welcome');
});


/*
|--------------------------------------------------------------------------
| Protected Routes (Authentication Required)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Email Verification Routes
    |--------------------------------------------------------------------------
    */
    Route::get('/email/verify', fn() => view('auth.verify-email'))->name('verification.notice');

    Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
        $request->fulfill();
        return redirect('/dashboard');
    })->middleware(['auth', 'signed'])->name('verification.verify');

    Route::post('/email/verification-notification', function (Request $request) {
        $request->user()->sendEmailVerificationNotification();
        return back()->with('message', 'Verification link sent!');
    })->middleware(['auth', 'throttle:6,1'])->name('verification.send');


    /*
    |--------------------------------------------------------------------------
    | Verified User Routes
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
        | Lead Routes
        |--------------------------------------------------------------------------
        */
        Route::get('/leads', [LeadController::class, 'index'])->name('leads.index');
        Route::get('/leads/create', [LeadController::class, 'create'])->name('leads.create');
        Route::post('/leads', [LeadController::class, 'store'])->name('leads.store');
        Route::get('/leads/{lead}/edit', [LeadController::class, 'edit'])->name('leads.edit');
        Route::put('/leads/{lead}', [LeadController::class, 'update'])->name('leads.update');
        Route::get('/leads/{id}/audits', [LeadController::class, 'showAudits'])->name('leads.audits');
        Route::get('/leads/export', [LeadController::class, 'export'])->name('leads.export');
        Route::post('/leads/reference-data', [LeadController::class, 'getQuoteReferenceData'])->name('leads.reference-data');
        Route::post('/leads/calculate-quote', [LeadController::class, 'calculateDraftQuote'])->name('leads.calculate-quote');
        Route::get('/leads/check-duplicate', [LeadController::class, 'checkDuplicate'])->name('leads.checkDuplicate');


        /*
        |--------------------------------------------------------------------------
        | Distance / Transport API Routes
        |--------------------------------------------------------------------------
        */
        Route::get('/api/distance/by-pincode', [DistanceController::class, 'byPincode'])->name('distance.byPincode');
        Route::get('/distance/calc', [DistanceController::class, 'calc'])->name('distance.calc');


        /*
        |--------------------------------------------------------------------------
        | Product Routes
        |--------------------------------------------------------------------------
        */
        Route::prefix('products')->name('products.')->group(function () {
            Route::get('/', [ProductController::class, 'index'])->name('index');
            Route::post('/', [ProductController::class, 'store'])->name('store');
            Route::get('/template/{id}/parameters', [ProductController::class, 'getParameters'])->name('getParameters');
            Route::put('/{product}/edit', [ProductController::class, 'edit'])->name('edit');
            Route::delete('/{id}', [ProductController::class, 'destroy'])->name('destroy');
        });

        Route::post('/units', [UnitController::class, 'store'])->name('units.store');
        Route::delete('/units/{id}', [UnitController::class, 'destroy'])->name('units.destroy');

        Route::post('/hsncodes', [HsncodeController::class, 'store'])->name('hsncodes.store');
        Route::delete('/hsncodes/{hsncode}', [HsncodeController::class, 'destroy'])->name('hsncodes.destroy');

        Route::resource('product-templates', ProductTemplateController::class)->only(['index', 'create', 'store', 'edit', 'update']);


        /*
        |--------------------------------------------------------------------------
        | Transport / TP Routes
        |--------------------------------------------------------------------------
        */
        Route::prefix('transport')->group(function () {

            // Offices
            Route::resource('offices', TpOfficeController::class)
                ->names([
                    'index' => 'tp_offices.index',
                    'create' => 'tp_offices.create',
                    'store' => 'tp_offices.store',
                    'update' => 'tp_offices.update',
                    'destroy' => 'tp_offices.destroy',
                ]);
            Route::get('get-districts', [TpOfficeController::class, 'getDistricts']);

            // District Rates
            Route::get('rates/get-districts', [TpDistrictRateController::class, 'getDistricts'])->name('rates.getDistricts');
            Route::get('rates/get-places', [TpDistrictRateController::class, 'getPlaces'])->name('rates.getPlaces');
            Route::resource('rates', TpDistrictRateController::class)
                ->except(['show'])
                ->names([
                    'index' => 'rates.index',
                    'create' => 'rates.create',
                    'store' => 'rates.store',
                    'edit' => 'rates.edit',
                    'update' => 'rates.update',
                    'destroy' => 'rates.destroy',
                ]);
            Route::get('rates/{id}/audits', [TpDistrictRateController::class, 'audits'])->name('rates.audits');

        });


        /*
        |--------------------------------------------------------------------------
        | Quotation Routes
        |--------------------------------------------------------------------------
        */
        Route::prefix('quotations')->name('quotations.')->group(function () {

            // List quotations
            Route::get('/', [QuotationController::class, 'index'])->name('index');

            // Create a brand-new quotation
            Route::get('/create', [QuotationController::class, 'create'])->name('create');
            Route::post('/store', [QuotationController::class, 'store'])->name('store');

            // ✅ NEW: "Create New Version" page (Blade)
            Route::get('/{id}/create-version', [QuotationController::class, 'createVersionPage'])
                ->name('create-version');

            // ✅ JSON data for existing quotation (used by AJAX)
            Route::get('/{id}/data', [QuotationController::class, 'fetchQuotationData'])
                ->name('data');

            // Version info, downloads, deletion
            Route::get('/version-data/{version}', [QuotationController::class, 'getVersionData'])->name('version-data');
            Route::get('/{quotation}/download', [QuotationController::class, 'download'])->name('download');
            Route::delete('/{id}', [QuotationController::class, 'destroy'])->name('destroy');
        });
        // web.php
        Route::get('/quotations/{quotation}/version/{version}/download', [QuotationController::class, 'downloadVersion'])
            ->name('quotations.download-version');

        /*
        |--------------------------------------------------------------------------
        | Admin Routes
        |--------------------------------------------------------------------------
        */
        Route::middleware(['role:admin'])->group(function () {

            Route::get('/users', [UserRoleController::class, 'index'])->name('users.list');
            Route::put('/users/{id}', [UserRoleController::class, 'role'])->name('users.update');

            // Admin can delete leads
            Route::delete('/leads/{id}', [LeadController::class, 'destroy'])->name('leads.destroy');
        });

    });

});
