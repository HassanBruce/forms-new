<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\TeamController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:login');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:register');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/', DashboardController::class)->name('dashboard');

    Route::get('/bookings', [BookingController::class, 'index'])->name('bookings.index');
    Route::post('/bookings', [BookingController::class, 'store'])->name('bookings.store');
    Route::get('/bookings/{booking}', [BookingController::class, 'show'])->name('bookings.show');
    Route::delete('/bookings/{booking}', [BookingController::class, 'destroy'])->name('bookings.destroy');
    Route::get('/bookings/{booking}/step/{step}', [BookingController::class, 'step'])->whereNumber('step')->name('bookings.step');
    Route::put('/bookings/{booking}/step/{step}', [BookingController::class, 'saveStep'])->whereNumber('step')->name('bookings.step.save');
    Route::patch('/bookings/{booking}/status', [BookingController::class, 'updateStatus'])->name('bookings.status');
    Route::get('/bookings/{booking}/invoice', [BookingController::class, 'invoice'])->name('bookings.invoice');
    Route::post('/bookings/{booking}/invoice/send', [BookingController::class, 'sendInvoice'])->name('bookings.invoice.send');

    Route::post('/bookings/{booking}/payments', [PaymentController::class, 'store'])->name('payments.store');
    Route::delete('/payments/{payment}', [PaymentController::class, 'destroy'])->name('payments.destroy');
    Route::get('/payments/{payment}/proof', [PaymentController::class, 'proof'])->name('payments.proof');
    Route::get('/payments', [PaymentController::class, 'index'])->name('payments.index');

    Route::get('/invoices', [BookingController::class, 'invoices'])->name('invoices.index');
    Route::get('/reports/profit-loss', [ReportController::class, 'profitLoss'])->name('reports.pl');

    Route::get('/team', [TeamController::class, 'index'])->name('team.index');
    Route::post('/team/members', [TeamController::class, 'storeMember'])->name('team.members.store');
    Route::put('/team/members/{user}', [TeamController::class, 'updateMember'])->name('team.members.update');
    Route::post('/team/teams', [TeamController::class, 'storeTeam'])->name('team.teams.store');

    Route::get('/brands', [BrandController::class, 'index'])->name('brands.index');
    Route::post('/brands', [BrandController::class, 'store'])->name('brands.store');
    Route::put('/brands/{brand}', [BrandController::class, 'update'])->name('brands.update');

    Route::get('/suppliers', [SupplierController::class, 'index'])->name('suppliers.index');
    Route::post('/suppliers', [SupplierController::class, 'store'])->name('suppliers.store');
    Route::delete('/suppliers/{supplier}', [SupplierController::class, 'destroy'])->name('suppliers.destroy');

    Route::get('/settings', [SettingsController::class, 'edit'])->name('settings.edit');
    Route::put('/settings', [SettingsController::class, 'update'])->name('settings.update');
});
