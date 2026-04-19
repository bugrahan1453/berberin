<?php

namespace App\Jobs;

use App\Models\Appointment;
use App\Services\TrustScoreService;
use App\Services\NotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

// Randevu saatinden X dakika sonra hâlâ gelmemiş müşterileri no-show olarak işaretler
class MarkNoShow implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public readonly int $appointmentId) {}

    public function handle(TrustScoreService $trustScore, NotificationService $notificationService): void
    {
        $appointment = Appointment::with(['user', 'shop'])->find($this->appointmentId);
        if (!$appointment) return;

        // Sadece hâlâ pending/confirmed ise no-show'a çevir
        if (!in_array($appointment->status, ['pending', 'confirmed'])) return;

        $appointment->update([
            'status' => 'no_show',
            'cancelled_at' => now(),
            'cancelled_by' => 'system',
        ]);

        // Güven skoru güncelle ve ceza uygula
        if ($appointment->user_id) {
            $trustScore->handleNoShow($appointment->user_id, $appointment->id);
        }

        // Dükkan sahibine bildirim
        $notificationService->send(
            targetType: 'shop',
            targetId: $appointment->shop_id,
            type: 'no_show',
            title: 'No-show tespit edildi',
            body: "Saat {$appointment->time} randevusu gelmedi olarak işaretlendi.",
            data: ['appointment_id' => $appointment->id]
        );
    }
}
