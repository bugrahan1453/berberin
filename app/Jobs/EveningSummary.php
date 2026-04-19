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

// Akşam özeti: günlük ciro ve tamamlanan randevuları bildirir
class EveningSummary implements ShouldQueue
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
            ->get();

        $completed = $appointments->where('status', 'completed')->count();
        $revenue = $appointments->where('status', 'completed')->sum('price');
        $noShow = $appointments->where('status', 'no_show')->count();

        $notificationService->send(
            targetType: 'shop',
            targetId: $shop->id,
            type: 'evening_summary',
            title: '🌙 Günlük Özet',
            body: "{$completed} randevu tamamlandı · {$revenue}₺ ciro · {$noShow} no-show",
            data: ['date' => $today, 'completed' => $completed, 'revenue' => $revenue, 'no_show' => $noShow]
        );
    }
}
