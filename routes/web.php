<?php

use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\ManagerProfileController;
use App\Http\Controllers\Admin\AmenitiesController;
use App\Http\Controllers\vendor\ReservationRecordController;
use App\Http\Controllers\Vendor\PaymentController;
use App\Http\Controllers\Customer\ProfileController;
use App\Http\Controllers\Customer\ReservationController;
use App\Http\Controllers\Customer\DownpaymentController;
use App\Http\Controllers\UserAuthController;
use App\Http\Controllers\EmptyyController;
use App\Http\Controllers\Customer\RegisteredUserController;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\ManagerMiddleware;
use App\Http\Middleware\VendorMiddleware;
use App\Models\Reservation;
use SebastianBergmann\CodeCoverage\Report\Html\Dashboard;

//  Home & Authentication Routes
Route::get('/', [UserAuthController::class, 'home'])->name('home');
Route::get('/login', [UserAuthController::class, 'create'])->name('login');
Route::post('/login', [UserAuthController::class, 'store']);
Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
Route::post('/register', [RegisteredUserController::class, 'store'])->name('cust.register');
Route::post('/logout', [UserAuthController::class, 'logout'])->name('logout');
Route::post('/send-reminder', [ReservationController::class, 'sendReminder'])->name('send-reminder');

// Customer Routes
Route::middleware('auth')->group(function () {
    Route::get('customer/profile', [ProfileController::class, 'view_profile'])
        ->name('customer.profile');
    Route::get('customer/dashboard', [ReservationController::class, 'create'])
        ->name('customer.dashboard');
    Route::get('customer/reservation', [ReservationController::class, 'create'])
        ->name('customer.reservation');
    Route::post('customer/reservation/store', [ReservationController::class, 'store'])
        ->middleware('auth')
        ->name('reservation.store');
    Route::post('customer/dashboard/reserve', [ReservationController::class, 'store'])
        ->name('customer.reserve');
    Route::get('customer/profile', [ProfileController::class, 'view_profile'])
        ->name('customer.profile');
    Route::get('customer/profile/{id}/edit', [ProfileController::class, 'edit_profile'])
        ->name('profile.edit');
    Route::patch('customer/profile/{id}/update', [ProfileController::class, 'update_profile'])
        ->name('profile.update');
    Route::get('customer/reservation-records', [ReservationController::class, 'view_reservations'])
        ->name('customer.reservation.records');
    Route::get('customer/balance', [ReservationController::class, 'view_balance'])
        ->name('customer.reservation.balance');
    Route::get('customer/check-availability', [ReservationController::class, 'checkAvailability']);
    Route::post('customer/reservation-records/{reservation}/cancel', [ReservationController::class, 'cancel_reservation'])
        ->name('cancel.reservation');
});

// Downpayment routes
Route::middleware(['auth'])->prefix('customer')->name('customer.')->group(function () {
    Route::get('/downpayment/{reservation}', [DownpaymentController::class, 'show'])
        ->name('downpayment.show');
    Route::post('/downpayment/{reservation}', [DownpaymentController::class, 'store'])
        ->name('downpayment.store');
});

//  Admin Routes
Route::middleware(AdminMiddleware::class)->group(function () {
    Route::get('admin/dashboard', [AdminDashboardController::class, 'create'])
        ->name('admin.dashboard');
    Route::get('/api/events', [ReservationRecordController::class, 'getEvents']);
});

// Manager Routes
Route::middleware(ManagerMiddleware::class)->group(function () {
    Route::get('admin/amenities/{type?}', [AmenitiesController::class, 'view_amenities'])
        ->name('admin.manager.amenities');
    Route::post('admin/amenities', [AmenitiesController::class, 'add_amenity'])
        ->name('amenities.store');
    Route::patch('admin/{type}s/{id}/update', [AmenitiesController::class, 'update_amenity'])
        ->name('amenities.update')
        ->where('type', '(cottage|table)');
    Route::patch('admin/amenities/{id}/archive', [AmenitiesController::class, 'archive'])
        ->name('amenitys.archive');
    Route::patch('admin/amenities/{id}/unarchive', [AmenitiesController::class, 'unarchive'])
        ->name('amenitys.unarchive');
    Route::get('admin/create-account', [AdminDashboardController::class, 'create_admin'])
        ->name('admin.create.account');
    Route::post('admin/create-account', [AdminDashboardController::class, 'store'])
        ->name('admin.create.account.store');
    Route::get('admin/reservation-list', [ReservationRecordController::class, 'view_reservation'])
        ->name('admin.reservation.list');
    Route::get('admin/all-reservations', [ReservationRecordController::class, 'view_all_reservations'])
        ->name('admin.all.reservations');
    Route::get('admin/manager-profile', [ManagerProfileController::class, 'view_profile'])
        ->name('admin.manager.profile');
    Route::put('admin/manager-profile/{id}/update', [ManagerProfileController::class, 'update_profile'])
        ->name('admin.manager.profile.update');
    Route::get('admin/vendors-list', [ManagerProfileController::class, 'view_vendors_list'])
        ->name('admin.vendors.list');
    Route::post('admin/vendors-list/{id}', [ManagerProfileController::class, 'update_vendors_list'])
        ->name('admin.vendors.list.promote');
    Route::get('admin/delete-requests', [ManagerProfileController::class, 'view_del_req'])
        ->name('admin.delete.requests');
});

//  Vendor Routes
Route::middleware(VendorMiddleware::class)->group(function () {
    Route::get('admin/vendor/amenities/{type?}', [AmenitiesController::class, 'view_amenities'])
        ->name('admin.vendor.amenities');
    Route::get('admin/vendor/calendar', [ReservationRecordController::class, 'view_reservation'])
        ->name('admin.vendor.reservation_calendar');
    Route::get('admin/vendor/reservation', [ReservationRecordController::class, 'view_all_reservations'])
        ->name('admin.vendor.reservation_records');
    Route::get('admin/vendor/balance', [ReservationRecordController::class, 'view_balance'])
        ->name('admin.vendor.remainingbal');
    Route::get('admin/vendor/calendar', [ReservationRecordController::class, 'view_reservation'])
        ->name('admin.vendor.reservation_calendar');
    Route::get('admin/vendor/reservation', [ReservationRecordController::class, 'view_history'])
        ->name('admin.vendor.reservation_records');
    Route::get('admin/vendor/balance', [ReservationRecordController::class, 'view_balance'])
        ->name('admin.vendor.remainingbal');
    Route::post('/admin/vendor/process-payment', [PaymentController::class, 'processPayment'])
        ->name('admin.vendor.process-payment');
    Route::post('/admin/vendor/invalid-payment', [PaymentController::class, 'invalidPayment'])
        ->name('admin.vendor.invalid-payment');
    Route::get('admin/vendor/edit-reservations', [ReservationRecordController::class, 'view_edit_reservations'])
        ->name('admin.vendor.edit-res-req');
    Route::get('/admin/vendor/walk-in', [ReservationRecordController::class, 'create_walkIn'])
        ->name('admin.vendor.walk_in');
    Route::post('/admin/vendor/walk-in', [ReservationRecordController::class, 'custom_walkIn'])
        ->name('admin.vendor.walk_in.store');
    Route::get('admin/vendor/payment/{reservation}', [ReservationRecordController::class, 'payment_show'])
        ->name('admin.vendor.reservations.payment.show');
});
