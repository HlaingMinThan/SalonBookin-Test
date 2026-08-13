<?php

use App\Http\Controllers\Admin\BookingController as AdminBookingController;
use App\Http\Controllers\Admin\ServiceController;
use App\Http\Controllers\Admin\SlotController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\BookingLookupController;
use App\Http\Controllers\ServiceApiController;
use App\Http\Controllers\SlotApiController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'Welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');
});

// Admin routes
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('services', [ServiceController::class, 'index'])->name('services.index');
    Route::post('services', [ServiceController::class, 'store'])->name('services.store');
    Route::put('services/{service}', [ServiceController::class, 'update'])->name('services.update');
    Route::delete('services/{service}', [ServiceController::class, 'destroy'])->name('services.destroy');

    Route::get('slots', [SlotController::class, 'index'])->name('slots.index');
    Route::post('slots', [SlotController::class, 'store'])->name('slots.store');
    Route::delete('slots/{slot}', [SlotController::class, 'destroy'])->name('slots.destroy');

    Route::get('bookings', [AdminBookingController::class, 'index'])->name('bookings.index');
    Route::put('bookings/{booking}/approve', [AdminBookingController::class, 'approve'])->name('bookings.approve');
    Route::put('bookings/{booking}/reject', [AdminBookingController::class, 'reject'])->name('bookings.reject');
});

// Customer API routes (JSON)
Route::get('api/services', [ServiceApiController::class, 'index'])->name('api.services.index');
Route::get('api/slots', [SlotApiController::class, 'index'])->name('api.slots.index');

// Customer booking routes
Route::post('book', [BookingController::class, 'store'])->name('book.store');
Route::get('book/{booking}', [BookingController::class, 'show'])->name('book.show');

// Customer booking lookup
Route::get('bookings', [BookingLookupController::class, 'index'])->name('bookings.index');
Route::post('bookings', [BookingLookupController::class, 'show'])->name('bookings.show');

require __DIR__.'/settings.php';
