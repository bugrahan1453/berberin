<?php

namespace App\Jobs;

use App\Models\Appointment;
use App\Models\Shop;
use App\Services\NotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;

// Sabah özeti: o günün randevularını dükkan sahibine bildirir
class MorningSummary implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public readonly int $shopId) {}

    public function handle(NotificationService $notificationService): void
    {
        $shop = Shop::find($this->shopId);
        if (!$shop) return;

        $today = Carbon::today()->toDateString();
        $appointments = Appointment::where('shop_id', $shop->id)
            ->where('date', $today)
            ->whereIn('status', ['pending', 'confirmed'])
            ->count();

        $notificationService->send(
            targetType: 'shop',
            targetId: $shop->id,
            type: 'morning_summary',
            title: "☀️ Günaydın! Bugünkü program",
            body: "Bugün {$appointments} randevunuz var. İyi çalışmalar!",
            data: ['date' => $today, 'count' => $appointments]
        );
    }
}
