<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Product\ProductController;
use App\Http\Controllers\Product\UnitController;
use App\Http\Controllers\Product\HsncodeController;
use App\Http\Controllers\Product\ProductTemplateController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
| This file is where you define all your web (browser-accessible) routes.
| Routes inside the 'auth' middleware block require a logged-in user.
*/

// 🌐 Public Route
Route::get('/', function () {
    return view('welcome');
});

// 📊 Dashboard
Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth'])
    ->name('dashboard');

// 🔒 Authenticated Routes
Route::middleware(['auth'])->group(function () {

    // 🙍‍♂️ Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // 📝 Leads
    Route::prefix('leads')->name('leads.')->group(function () {
        Route::get('/', [LeadController::class, 'index'])->name('index');
        Route::get('/create', fn() => view('leads.create'))->name('create');
        Route::post('/', [LeadController::class, 'store'])->name('store');
        Route::get('{id}/edit', [LeadController::class, 'edit'])->name('edit');
        Route::put('{id}', [LeadController::class, 'update'])->name('update');
        Route::put('{id}/full-update', [LeadController::class, 'updateFull'])->name('update.full');
        Route::delete('{id}', [LeadController::class, 'destroy'])->name('destroy');
        Route::get('{id}/audits', [LeadController::class, 'showAudits'])->name('audits');
    });

    // 📦 Products
    Route::prefix('products')->name('products.')->group(function () {
        Route::get('/', [ProductController::class, 'index'])->name('index');
        Route::post('/', [ProductController::class, 'store'])->name('store');
        Route::get('/template/{id}/parameters', [ProductController::class, 'getParameters'])->name('getParameters');
    });

    // 🧪 Units (used by AJAX modal)
    Route::post('/units', [UnitController::class, 'store'])->name('units.store');

    // 🧾 HSN Codes (used by AJAX modal)
    Route::post('/hsncodes', [HsncodeController::class, 'store'])->name('hsncodes.store');

    // 📋 Product Templates (optional - if you're managing templates)
    Route::resource('product-templates', ProductTemplateController::class)->only(['index', 'create', 'store', 'edit', 'update']);
});

// 🔐 Auth scaffolding (login, register, forgot password, etc.)
require __DIR__.'/auth.php';
