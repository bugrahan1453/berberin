<?php

namespace App\Jobs;

use App\Models\Appointment;
use App\Services\NotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

// Randevu hatırlatma: 24 saat, 2 saat ve 30 dakika önce gönderilir
class SendReminderNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public readonly int $appointmentId,
        public readonly string $reminderType // '24h' | '2h' | '30m'
    ) {}

    public function handle(NotificationService $notificationService): void
    {
        $appointment = Appointment::with(['user', 'shop', 'service'])->find($this->appointmentId);
        if (!$appointment || !in_array($appointment->status, ['confirmed', 'pending'])) return;

        $shopName = $appointment->shop->name;
        $time = $appointment->time;
        $date = $appointment->date;

        [$title, $body] = match ($this->reminderType) {
            '24h' => ["Randevu Hatırlatma ⏰", "Yarın {$time}'de {$shopName} randevunuz var."],
            '2h' => ["Randevunuz Yaklaşıyor!", "{$shopName} randevunuza 2 saat kaldı. Gelecek misiniz?"],
            '30m' => ["30 Dakika Kaldı! 🏃", "{$shopName} randevunuza 30 dakika kaldı!"],
            default => ['Randevu Hatırlatma', 'Randevunuz yaklaşıyor.'],
        };

        if ($appointment->user_id) {
            $notificationService->send(
                targetType: 'user',
                targetId: $appointment->user_id,
                type: "reminder_{$this->reminderType}",
                title: $title,
                body: $body,
                data: ['appointment_id' => $appointment->id]
            );
        }
    }
}
