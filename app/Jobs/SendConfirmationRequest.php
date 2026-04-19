<?php

namespace App\Jobs;

use App\Models\Appointment;
use App\Services\NotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

// 2 saat önce müşteriye "Gelecek misiniz?" bildirimi gönderir
class SendConfirmationRequest implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public readonly int $appointmentId) {}

    public function handle(NotificationService $notificationService): void
    {
        $appointment = Appointment::with(['user', 'shop'])->find($this->appointmentId);
        if (!$appointment || !in_array($appointment->status, ['confirmed', 'pending'])) return;

        $shopName = $appointment->shop->name;

        if ($appointment->user_id) {
            $notificationService->send(
                targetType: 'user',
                targetId: $appointment->user_id,
                type: 'confirmation_request',
                title: 'Randevunuza gelecek misiniz?',
                body: "{$shopName} randevunuza 2 saat kaldı. Evet mi Hayır mı?",
                data: [
                    'appointment_id' => $appointment->id,
                    'action_required' => true,
                ]
            );
        }
    }
}
