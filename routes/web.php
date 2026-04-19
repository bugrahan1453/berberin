<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Panel\AuthController;
use App\Http\Controllers\Panel\DashboardController;
use App\Http\Controllers\Panel\PanelController;

// Ana sayfa — Müşteri PWA
Route::get('/', function () {
    return response()->file(public_path('pwa/index.html'));
});

// PWA Service Worker (root'tan erişilmeli)
Route::get('/sw.js', function () {
    return response()->file(public_path('pwa/sw.js'), ['Content-Type' => 'application/javascript']);
});

// PWA manifest
Route::get('/manifest.json', function () {
    return response()->file(public_path('pwa/manifest.json'), ['Content-Type' => 'application/manifest+json']);
});

// Müşteri PWA (dükkan sayfası QR kodundan açılır)
// PWA ayrı statik sunucuda olduğunda bu route kaldırılabilir
Route::get('/s/{slug}', function ($slug) {
    // PWA public/pwa klasöründe ise serve et, yoksa API'den yönlendir
    $pwaIndex = public_path('../../../pwa/index.html');
    if (file_exists($pwaIndex)) {
        return response()->file($pwaIndex);
    }
    return redirect("/api/shops/{$slug}");
})->name('shop.pwa');

// --- Panel Auth ---
Route::prefix('panel')->name('panel.')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::get('login', [AuthController::class, 'loginForm'])->name('login');
        Route::post('login', [AuthController::class, 'login'])->name('login.post');
        Route::get('register', [AuthController::class, 'registerForm'])->name('register');
        Route::post('register', [AuthController::class, 'register'])->name('register.post');
    });

    Route::post('logout', [AuthController::class, 'logout'])->name('logout');

    // Dükkan kurulum (auth ama shop yok)
    Route::middleware('auth')->group(function () {
        Route::get('setup', [AuthController::class, 'setupForm'])->name('setup');
        Route::post('setup', [AuthController::class, 'setup'])->name('setup.post');
    });

    // --- Panel sayfaları (auth + shop sahibi) ---
    Route::middleware(['auth'])->group(function () {
        Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

        Route::get('appointments', [PanelController::class, 'appointments'])->name('appointments');
        Route::post('appointments', [PanelController::class, 'storeAppointment'])->name('appointments.store');
        Route::patch('appointments', [PanelController::class, 'updateAppointmentStatus'])->name('appointments.status');
        Route::get('seats', [PanelController::class, 'seats'])->name('seats');
        Route::post('seats', [PanelController::class, 'storeSeat'])->name('seats.store');
        Route::patch('seats/{id}/status', [PanelController::class, 'updateSeatStatus'])->name('seats.status');
        Route::post('seats/toggle', [PanelController::class, 'toggleShopFull'])->name('seats.toggle');
        Route::get('staff', [PanelController::class, 'staff'])->name('staff');
        Route::post('staff', [PanelController::class, 'storeStaff'])->name('staff.store');
        Route::patch('staff/{id}/toggle', [PanelController::class, 'toggleStaff'])->name('staff.toggle');
        Route::delete('staff/{id}', [PanelController::class, 'deleteStaff'])->name('staff.delete');
        Route::get('staff/{id}/shifts', [PanelController::class, 'staffShifts'])->name('staff.shifts');
        Route::post('staff/{id}/shifts', [PanelController::class, 'saveStaffShifts'])->name('staff.shifts.save');
        Route::get('services', [PanelController::class, 'services'])->name('services');
        Route::post('services', [PanelController::class, 'storeService'])->name('services.store');
        Route::put('services/{id}', [PanelController::class, 'updateService'])->name('services.update');
        Route::patch('services/{id}/toggle', [PanelController::class, 'toggleService'])->name('services.toggle');
        Route::delete('services/{id}', [PanelController::class, 'deleteService'])->name('services.delete');
        Route::get('gallery', [PanelController::class, 'gallery'])->name('gallery');
        Route::post('gallery', [PanelController::class, 'storeGallery'])->name('gallery.store');
        Route::delete('gallery/{id}', [PanelController::class, 'deleteGallery'])->name('gallery.delete');
        Route::get('reviews', [PanelController::class, 'reviews'])->name('reviews');
        Route::post('reviews/{id}/reply', [PanelController::class, 'replyReview'])->name('reviews.reply');
        Route::get('reports', [PanelController::class, 'reports'])->name('reports');
        Route::get('settings', [PanelController::class, 'settings'])->name('settings');
        Route::post('settings', [PanelController::class, 'settingsUpdate'])->name('settings.update');
        Route::post('settings/notifications', [PanelController::class, 'notificationSettingsUpdate'])->name('settings.notifications');
        Route::get('qr-code', [PanelController::class, 'qrCode'])->name('qr');
    });
});
