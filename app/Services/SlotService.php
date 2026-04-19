<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Service;
use App\Models\Shop;
use Illuminate\Support\Carbon;

class SlotService
{
    /**
     * Belirli bir dükkan ve tarih için müsait slot listesini döner.
     */
    public function getAvailableSlots(Shop $shop, string $date, int $serviceId): array
    {
        $service = Service::find($serviceId);
        if (!$service) {
            return [];
        }

        $settings = $shop->settings;
        $slotInterval = $settings?->slot_interval ?? 30;
        $minAdvanceHours = $settings?->min_advance_hours ?? 2;
        $maxAdvanceDays = $settings?->max_advance_days ?? 14;

        $requestedDate = Carbon::parse($date);
        $today = Carbon::today();

        // Tarih sınırları kontrolü
        if ($requestedDate->lt($today)) {
            return [];
        }

        if ($requestedDate->diffInDays($today) > $maxAdvanceDays) {
            return [];
        }

        // O günün çalışma saatlerini bul (0=Pzt, 6=Paz)
        $dayOfWeek = $requestedDate->dayOfWeek === 0 ? 6 : $requestedDate->dayOfWeek - 1;
        $shopHour = $shop->hours->where('day_of_week', $dayOfWeek)->first();

        if (!$shopHour || $shopHour->is_closed) {
            return [];
        }

        $openTime = Carbon::parse($date . ' ' . $shopHour->open_time);
        $closeTime = Carbon::parse($date . ' ' . $shopHour->close_time);

        // Mevcut randevuları getir
        $existingAppointments = Appointment::where('shop_id', $shop->id)
            ->where('date', $date)
            ->whereNotIn('status', ['cancelled', 'no_show'])
            ->get();

        $slots = [];
        $current = $openTime->copy();

        while ($current->lt($closeTime)) {
            $slotEnd = $current->copy()->addMinutes($service->duration_min);

            // Bitiş zamanı çalışma bitiş saatini geçmesin
            if ($slotEnd->gt($closeTime)) {
                break;
            }

            // Mola saatlerini kontrol et
            if ($shopHour->break_start && $shopHour->break_end) {
                $breakStart = Carbon::parse($date . ' ' . $shopHour->break_start);
                $breakEnd = Carbon::parse($date . ' ' . $shopHour->break_end);

                if ($current->lt($breakEnd) && $slotEnd->gt($breakStart)) {
                    $current->addMinutes($slotInterval);
                    continue;
                }
            }

            // Minimum peşin süre kontrolü (bugün için)
            if ($requestedDate->isToday()) {
                if ($current->lt(Carbon::now()->addHours($minAdvanceHours))) {
                    $current->addMinutes($slotInterval);
                    continue;
                }
            }

            // Bu slotta çakışan randevu var mı?
            $isAvailable = !$existingAppointments->contains(function ($appt) use ($current, $slotEnd) {
                $apptStart = Carbon::parse($appt->time);
                $apptEnd = Carbon::parse($appt->end_time);
                return $current->lt($apptEnd) && $slotEnd->gt($apptStart);
            });

            $slots[] = [
                'time' => $current->format('H:i'),
                'end_time' => $slotEnd->format('H:i'),
                'available' => $isAvailable,
            ];

            $current->addMinutes($slotInterval);
        }

        return $slots;
    }
}
