<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LeadController;
use Illuminate\Support\Facades\Auth;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth'])->group(function () {

    // 🌟 Dashboard
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // 📝 Lead Routes
    Route::get('/leads', [LeadController::class, 'showAll'])->name('leads.index');
    Route::get('/leads/create', fn() => view('leads.create'))->name('leads.create');
    Route::post('/leads', [LeadController::class, 'store'])->name('leads.store');
    Route::put('/leads/{id}', [LeadController::class, 'update'])->name('leads.update');
    Route::put('/leads/{id}/full-update', [LeadController::class, 'updateFull'])->name('leads.update.full');
    Route::get('/leads/{id}/edit', [LeadController::class, 'edit'])->name('leads.edit');

    // 🛡️ Soft delete route (only for admins)
    Route::delete('/leads/{id}', [LeadController::class, 'destroy'])
        ->name('leads.destroy');

    // 🧾 Audit logs
    Route::get('/leads/{id}/audits', [LeadController::class, 'showAudits'])->name('leads.audits');
});

require __DIR__.'/auth.php';
