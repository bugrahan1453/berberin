<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\OtpController;
use App\Http\Controllers\Customer\ExploreController;
use App\Http\Controllers\Customer\BookingController;
use App\Http\Controllers\Customer\FavoriteController;
use App\Http\Controllers\Customer\NotificationController;
use App\Http\Controllers\Customer\ProfileController;
use App\Http\Controllers\Shop\ShopController;
use App\Http\Controllers\Shop\SeatController;
use App\Http\Controllers\Shop\StaffController;
use App\Http\Controllers\Shop\ServiceController;
use App\Http\Controllers\Shop\AppointmentController;
use App\Http\Controllers\Shop\ReviewController;
use App\Http\Controllers\Shop\GalleryController;
use App\Http\Controllers\Shop\QueueController;
use App\Http\Controllers\Shop\SettingsController;
use App\Http\Controllers\Shop\ReportController;

// --- Auth ---
Route::prefix('auth')->group(function () {
    Route::post('register', RegisterController::class)->middleware('throttle:5,1');
    Route::post('login', LoginController::class)->middleware('throttle:10,1');
    Route::post('send-otp', [OtpController::class, 'send'])->middleware('throttle:3,1');
    Route::post('verify-otp', [OtpController::class, 'verify'])->middleware('throttle:5,1');
    Route::post('logout', [OtpController::class, 'logout'])->middleware('auth:sanctum');
});

// --- Müşteri: Halka Açık ---
Route::prefix('shops')->group(function () {
    Route::get('/', [ExploreController::class, 'index']);
    Route::get('{slug}', [ExploreController::class, 'show']);
    Route::get('{id}/services', [ExploreController::class, 'services']);
    Route::get('{id}/staff', [ExploreController::class, 'staff']);
    Route::get('{id}/reviews', [ExploreController::class, 'reviews']);
    Route::get('{id}/gallery', [ExploreController::class, 'gallery']);
    Route::get('{id}/available-slots', [ExploreController::class, 'availableSlots']);
    Route::get('{id}/queue-status', [ExploreController::class, 'queueStatus']);
});

// --- Müşteri: Giriş Gerekli ---
Route::middleware('auth:sanctum')->group(function () {
    // Randevular
    Route::get('bookings', [BookingController::class, 'index']);
    Route::post('bookings', [BookingController::class, 'store']);
    Route::get('bookings/{id}', [BookingController::class, 'show']);
    Route::put('bookings/{id}/cancel', [BookingController::class, 'cancel']);
    Route::put('bookings/{id}/confirm', [BookingController::class, 'confirm']);

    // Yorumlar
    Route::post('reviews', [ReviewController::class, 'storeCustomer']);

    // Favoriler
    Route::get('favorites', [FavoriteController::class, 'index']);
    Route::post('favorites/{shopId}', [FavoriteController::class, 'store']);
    Route::delete('favorites/{shopId}', [FavoriteController::class, 'destroy']);

    // Bildirimler
    Route::get('notifications', [NotificationController::class, 'index']);
    Route::put('notifications/{id}/read', [NotificationController::class, 'markRead']);

    // Profil
    Route::get('profile', [ProfileController::class, 'show']);
    Route::put('profile', [ProfileController::class, 'update']);
});

// --- Dükkan Paneli API ---
Route::prefix('shop')->middleware('auth:sanctum')->group(function () {
    // Dashboard
    Route::get('dashboard', [ShopController::class, 'dashboard']);

    // Randevular
    Route::get('appointments', [AppointmentController::class, 'index']);
    Route::post('appointments', [AppointmentController::class, 'store']);
    Route::put('appointments/{id}/status', [AppointmentController::class, 'updateStatus']);

    // Koltuklar
    Route::get('seats', [SeatController::class, 'index']);
    Route::post('seats', [SeatController::class, 'store']);
    Route::put('seats/{id}', [SeatController::class, 'update']);
    Route::put('seats/{id}/status', [SeatController::class, 'updateStatus']);
    Route::delete('seats/{id}', [SeatController::class, 'destroy']);

    // Personel
    Route::get('staff', [StaffController::class, 'index']);
    Route::post('staff', [StaffController::class, 'store']);
    Route::put('staff/{id}', [StaffController::class, 'update']);
    Route::delete('staff/{id}', [StaffController::class, 'destroy']);
    Route::get('staff/{id}/shifts', [StaffController::class, 'shifts']);
    Route::post('staff/{id}/shifts', [StaffController::class, 'storeShift']);

    // Hizmetler
    Route::get('services', [ServiceController::class, 'index']);
    Route::post('services', [ServiceController::class, 'store']);
    Route::put('services/{id}', [ServiceController::class, 'update']);
    Route::delete('services/{id}', [ServiceController::class, 'destroy']);

    // Yorumlar
    Route::get('reviews', [ReviewController::class, 'shopIndex']);
    Route::put('reviews/{id}/reply', [ReviewController::class, 'reply']);

    // Ayarlar
    Route::get('settings', [SettingsController::class, 'show']);
    Route::put('settings', [SettingsController::class, 'update']);
    Route::get('notification-settings', [SettingsController::class, 'notificationShow']);
    Route::put('notification-settings', [SettingsController::class, 'notificationUpdate']);

    // Raporlar
    Route::get('reports', [ReportController::class, 'index']);

    // Sıra & doluluk
    Route::put('queue-status', [QueueController::class, 'update']);

    // Galeri
    Route::get('gallery', [GalleryController::class, 'index']);
    Route::post('gallery', [GalleryController::class, 'store']);
    Route::delete('gallery/{id}', [GalleryController::class, 'destroy']);

    // QR kod
    Route::get('qr-code', [ShopController::class, 'qrCode']);
});
