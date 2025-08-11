<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserRoleController;

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

    //Lead Routes
    //Display Index
    Route::get('/leads', [LeadController::class, 'index'])->name('leads.index');
    //Display Edit
    Route::get('/leads/{lead}/edit', [LeadController::class, 'edit'])->name('leads.edit');
    //Store Edit
    Route::put('/leads/{lead}', [LeadController::class, 'update'])->name('leads.update');

    //Display Create
    Route::get('/leads/create', [LeadController::class, 'create'])->name('leads.create');              // Show create form
    //Store Create
    Route::post('/leads', [LeadController::class, 'store'])->name('leads.store');                    // Store new lead

    //Delete a lead
    Route::delete('/leads/{id}', [LeadController::class, 'destroy'])->name('leads.destroy');         // Delete lead
    // Audit Logs
    Route::get('/leads/{id}/audits', [LeadController::class, 'showAudits'])->name('leads.audits');   // View audits

   // User roles (accessible to users with "owner" or "admin" role)
   Route::middleware(['role:admin'])->group(function () {
       Route::get('/users', [UserRoleController::class, 'index'])->name('users.list');
       Route::put('/users/{id}', [UserRoleController::class, 'role'])->name('users.update');
   });

});

// 🔐 Auth Routes (login, register, forgot password, etc.)
require __DIR__.'/auth.php';

