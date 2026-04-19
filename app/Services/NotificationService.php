<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    public function send(
        string $targetType,
        int $targetId,
        string $type,
        string $title,
        string $body,
        array $data = [],
        string $channel = 'push'
    ): Notification {
        $notification = Notification::create([
            'target_type' => $targetType,
            'target_id' => $targetId,
            'channel' => $channel,
            'type' => $type,
            'title' => $title,
            'body' => $body,
            'data' => $data,
            'sent_at' => now(),
        ]);

        // FCM push bildirimi gönder
        if ($channel === 'push' || $channel === 'both') {
            $this->sendFcm($targetType, $targetId, $title, $body, $data);
        }

        // SMS gönder (kullanıcı kanalı için)
        if (($channel === 'sms' || $channel === 'both') && $targetType === 'user') {
            $this->sendSms($targetId, $body);
        }

        return $notification;
    }

    public function notifyUser(int $userId, string $type, string $title, string $body, array $data = []): Notification
    {
        return $this->send('user', $userId, $type, $title, $body, $data);
    }

    public function notifyShop(int $shopId, string $type, string $title, string $body, array $data = []): Notification
    {
        return $this->send('shop', $shopId, $type, $title, $body, $data);
    }

    // ── FCM (Firebase Cloud Messaging) ───────────────────────────
    private function sendFcm(string $targetType, int $targetId, string $title, string $body, array $data): void
    {
        $fcmToken = $this->getFcmToken($targetType, $targetId);
        if (!$fcmToken) return;

        $serverKey = config('services.fcm.server_key');
        if (!$serverKey) return;

        try {
            Http::withHeaders(['Authorization' => "key={$serverKey}"])
                ->post('https://fcm.googleapis.com/fcm/send', [
                    'to' => $fcmToken,
                    'notification' => ['title' => $title, 'body' => $body, 'sound' => 'default'],
                    'data' => array_merge($data, ['click_action' => 'FLUTTER_NOTIFICATION_CLICK']),
                    'priority' => 'high',
                ]);
        } catch (\Exception $e) {
            Log::warning('FCM gönderim hatası', ['error' => $e->getMessage()]);
        }
    }

    // ── NetGSM SMS ────────────────────────────────────────────────
    private function sendSms(int $userId, string $message): void
    {
        $user = User::find($userId);
        if (!$user?->phone) return;

        $username = config('services.netgsm.username');
        $password = config('services.netgsm.password');
        $header = config('services.netgsm.header', 'BERBERIN');

        if (!$username || !$password) return;

        $phone = preg_replace('/\D/', '', $user->phone);
        if (strlen($phone) === 10) $phone = '90' . $phone;
        elseif (strlen($phone) === 11 && str_starts_with($phone, '0')) $phone = '9' . $phone;

        try {
            Http::get('https://api.netgsm.com.tr/sms/send/get/', [
                'usercode' => $username,
                'password' => $password,
                'gsmno' => $phone,
                'message' => mb_substr($message, 0, 160),
                'msgheader' => $header,
                'dil' => 'TR',
            ]);
        } catch (\Exception $e) {
            Log::warning('NetGSM SMS hatası', ['error' => $e->getMessage(), 'phone' => $phone]);
        }
    }

    private function getFcmToken(string $targetType, int $targetId): ?string
    {
        if ($targetType === 'user') {
            return User::find($targetId)?->fcm_token;
        }
        // Dükkan sahibinin FCM token'ı
        $shop = \App\Models\Shop::find($targetId);
        return $shop ? User::find($shop->owner_id)?->fcm_token : null;
    }
}
