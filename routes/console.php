<?php

use App\Jobs\EveningSummary;
use App\Jobs\MarkNoShow;
use App\Jobs\MorningSummary;
use App\Jobs\SendReminderNotification;
use App\Models\Appointment;
use App\Models\NotificationSetting;
use App\Models\Shop;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schedule;

// ── Sabah özetleri (her dükkanın kendi saatinde) ──────────────────
Schedule::call(function () {
    $now = now()->format('H:i');
    NotificationSetting::where('morning_summary_enabled', true)
        ->whereRaw("TIME_FORMAT(morning_summary_time, '%H:%i') = ?", [$now])
        ->get()
        ->each(fn($s) => MorningSummary::dispatch($s->shop_id));
})->everyMinute()->name('morning-summary')->withoutOverlapping();

// ── Akşam özetleri (20:00 sabit) ─────────────────────────────────
Schedule::call(function () {
    NotificationSetting::where('evening_summary_enabled', true)
        ->get()
        ->each(fn($s) => EveningSummary::dispatch($s->shop_id));
})->dailyAt('20:00')->name('evening-summary');

// ── Randevu hatırlatmaları (her dakika kontrol) ───────────────────
Schedule::call(function () {
    $now = now();

    // 24 saat sonraki randevular
    $target24h = $now->copy()->addHours(24);
    Appointment::whereIn('status', ['confirmed', 'pending'])
        ->where('date', $target24h->toDateString())
        ->whereTime('time', $target24h->format('H:i'))
        ->each(fn($a) => SendReminderNotification::dispatch($a->id, '24h'));

    // 2 saat sonraki randevular
    $target2h = $now->copy()->addHours(2);
    Appointment::whereIn('status', ['confirmed', 'pending'])
        ->where('date', $target2h->toDateString())
        ->whereTime('time', $target2h->format('H:i'))
        ->each(fn($a) => SendReminderNotification::dispatch($a->id, '2h'));

    // 30 dakika sonraki randevular
    $target30m = $now->copy()->addMinutes(30);
    Appointment::whereIn('status', ['confirmed', 'pending'])
        ->where('date', $target30m->toDateString())
        ->whereTime('time', $target30m->format('H:i'))
        ->each(fn($a) => SendReminderNotification::dispatch($a->id, '30m'));
})->everyMinute()->name('appointment-reminders')->withoutOverlapping();

// ── No-show otomatik işaretleme ───────────────────────────────────
Schedule::call(function () {
    $now = now();

    // Her dükkanın no_show_auto_mark_minutes ayarına göre işaretle
    \App\Models\NotificationSetting::all()->each(function ($setting) use ($now) {
        $minutesAgo = $now->copy()->subMinutes($setting->no_show_auto_mark_minutes);
        Appointment::where('shop_id', $setting->shop_id)
            ->whereIn('status', ['confirmed', 'pending'])
            ->where('date', $minutesAgo->toDateString())
            ->whereTime('time', '<=', $minutesAgo->format('H:i'))
            ->each(fn($a) => MarkNoShow::dispatch($a->id));
    });
})->everyMinute()->name('no-show-marking')->withoutOverlapping();
