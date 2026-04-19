<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Shop;
use App\Services\QrService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class ShopController extends Controller
{
    private function getOwnerShop(): Shop
    {
        return Shop::where('owner_id', auth()->id())->firstOrFail();
    }

    public function dashboard()
    {
        $shop = $this->getOwnerShop();
        $today = Carbon::today()->toDateString();

        $todayAppointments = Appointment::where('shop_id', $shop->id)
            ->where('date', $today)
            ->count();

        $todayRevenue = Appointment::where('shop_id', $shop->id)
            ->where('date', $today)
            ->where('status', 'completed')
            ->sum('price');

        $seats = $shop->seats()->with('assignedStaff')->get();

        $upcomingAppointments = Appointment::with(['user:id,name,phone', 'service:id,name', 'staff:id,name', 'seat:id,name'])
            ->where('shop_id', $shop->id)
            ->where('date', $today)
            ->whereIn('status', ['pending', 'confirmed'])
            ->orderBy('time')
            ->limit(10)
            ->get();

        // Son 7 günlük grafik verisi
        $weeklyData = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i)->toDateString();
            $weeklyData[] = [
                'date' => $date,
                'appointments' => Appointment::where('shop_id', $shop->id)->where('date', $date)->count(),
                'revenue' => Appointment::where('shop_id', $shop->id)->where('date', $date)->where('status', 'completed')->sum('price'),
            ];
        }

        $totalSeats = $seats->count();
        $busySeats = $seats->whereIn('status', ['busy', 'reserved'])->count();
        $occupancyRate = $totalSeats > 0 ? round(($busySeats / $totalSeats) * 100) : 0;

        return response()->json([
            'success' => true,
            'data' => [
                'shop' => $shop,
                'today_appointments' => $todayAppointments,
                'today_revenue' => $todayRevenue,
                'occupancy_rate' => $occupancyRate,
                'seats' => $seats,
                'upcoming_appointments' => $upcomingAppointments,
                'weekly_data' => $weeklyData,
            ],
            'message' => '',
        ]);
    }

    public function qrCode()
    {
        $shop = $this->getOwnerShop();
        $qrUrl = app(QrService::class)->generate($shop);

        return response()->json([
            'success' => true,
            'data' => ['qr_url' => $qrUrl, 'shop_url' => url('/s/' . $shop->slug)],
            'message' => '',
        ]);
    }
}
