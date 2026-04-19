<?php

namespace App\Services;

use App\Models\NoShowLog;
use App\Models\User;

class TrustScoreService
{
    // Güven skoru değişim miktarları
    const ATTENDED = 5;
    const REVIEWED = 2;
    const THREE_MONTH_ACTIVE = 10;
    const NO_SHOW = -20;
    const LATE_CANCEL = -10;

    public function addPoints(User $user, int $points): void
    {
        $newScore = min(100, max(0, $user->trust_score + $points));
        $user->update(['trust_score' => $newScore]);
    }

    // userId veya User nesnesi kabul eder
    public function handleNoShow(int|User $userOrId, int $appointmentId): void
    {
        $user = $userOrId instanceof User ? $userOrId : User::find($userOrId);
        if (!$user) return;

        $this->addPoints($user, self::NO_SHOW);

        // No-show kaydı oluştur veya güncelle
        $log = NoShowLog::where('user_id', $user->id)->latest()->first();
        $noShowCount = NoShowLog::where('user_id', $user->id)->count() + 1;

        $penaltyType = null;
        $penaltyUntil = null;

        switch ($noShowCount) {
            case 1:
                $penaltyType = 'warning';
                break;
            case 2:
                $penaltyType = 'ban_24h';
                $penaltyUntil = now()->addHours(24);
                break;
            case 3:
                $penaltyType = 'ban_7d';
                $penaltyUntil = now()->addDays(7);
                break;
            case 4:
                $penaltyType = 'ban_30d';
                $penaltyUntil = now()->addDays(30);
                break;
            default:
                $penaltyType = 'permanent';
                $penaltyUntil = now()->addYears(100);
                $user->update(['status' => 'banned']);
        }

        NoShowLog::create([
            'user_id' => $user->id,
            'appointment_id' => $appointmentId,
            'total_count' => $noShowCount,
            'penalty_type' => $penaltyType,
            'penalty_until' => $penaltyUntil,
            'created_at' => now(),
        ]);
    }
}
