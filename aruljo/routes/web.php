<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\ProfileController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you register web routes for your application. These
| routes are loaded by the RouteServiceProvider and assigned to the "web"
| middleware group. Make something great!
|
*/

// 🔰 Public Route
Route::get('/', function () {
    return view('welcome');
});

// 📊 Dashboard (accessible only after login)
Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth'])
    ->name('dashboard');

// 🔒 Protected Routes (Only for Authenticated Users)
Route::middleware(['auth'])->group(function () {

    // 🙍‍♂️ Profile Routes
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // 📝 Lead Routes
    Route::get('/leads', [LeadController::class, 'index'])->name('leads.index');                     // List leads
    Route::get('/leads/create', fn() => view('leads.create'))->name('leads.create');                 // Show create form
    Route::post('/leads', [LeadController::class, 'store'])->name('leads.store');                    // Store new lead
    Route::get('/leads/{id}/edit', [LeadController::class, 'edit'])->name('leads.edit');             // Edit lead
    Route::put('/leads/{id}', [LeadController::class, 'update'])->name('leads.update');              // Partial update
    Route::put('/leads/{id}/full-update', [LeadController::class, 'updateFull'])->name('leads.update.full'); // Full update
    Route::delete('/leads/{id}', [LeadController::class, 'destroy'])->name('leads.destroy');         // Delete lead

    // 🧾 Audit Logs
    Route::get('/leads/{id}/audits', [LeadController::class, 'showAudits'])->name('leads.audits');   // View audits
});

// 🔐 Auth Routes (login, register, forgot password, etc.)
require __DIR__.'/auth.php';
