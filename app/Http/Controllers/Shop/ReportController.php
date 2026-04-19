<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Shop;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class ReportController extends Controller
{
    private function getOwnerShop(): Shop
    {
        return Shop::where('owner_id', auth()->id())->firstOrFail();
    }

    public function index(Request $request)
    {
        $shop = $this->getOwnerShop();

        // Pro+ abonelik kontrolü
        if ($shop->subscription_plan === 'starter') {
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => 'Bu özellik Pro veya Premium abonelik gerektirir.',
            ], 403);
        }

        $period = $request->period ?? 'weekly';

        [$startDate, $endDate] = match ($period) {
            'daily' => [Carbon::today(), Carbon::today()],
            'monthly' => [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()],
            default => [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()],
        };

        $appointments = Appointment::where('shop_id', $shop->id)
            ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
            ->get();

        $totalRevenue = $appointments->where('status', 'completed')->sum('price');
        $totalAppointments = $appointments->count();
        $completedCount = $appointments->where('status', 'completed')->count();
        $noShowCount = $appointments->where('status', 'no_show')->count();
        $cancelledCount = $appointments->whereIn('status', ['cancelled'])->count();

        $noShowRate = $totalAppointments > 0 ? round(($noShowCount / $totalAppointments) * 100, 1) : 0;

        // Personel bazlı performans
        $staffPerformance = Appointment::where('shop_id', $shop->id)
            ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
            ->where('status', 'completed')
            ->with('staff:id,name')
            ->get()
            ->groupBy('staff_id')
            ->map(function ($items) {
                return [
                    'staff' => $items->first()?->staff,
                    'count' => $items->count(),
                    'revenue' => $items->sum('price'),
                ];
            })
            ->values();

        // En popüler hizmetler
        $popularServices = Appointment::where('shop_id', $shop->id)
            ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
            ->with('service:id,name')
            ->get()
            ->groupBy('service_id')
            ->map(function ($items) {
                return [
                    'service' => $items->first()?->service,
                    'count' => $items->count(),
                ];
            })
            ->sortByDesc('count')
            ->values()
            ->take(5);

        return response()->json([
            'success' => true,
            'data' => [
                'period' => $period,
                'start_date' => $startDate->toDateString(),
                'end_date' => $endDate->toDateString(),
                'total_revenue' => $totalRevenue,
                'total_appointments' => $totalAppointments,
                'completed_count' => $completedCount,
                'no_show_count' => $noShowCount,
                'cancelled_count' => $cancelledCount,
                'no_show_rate' => $noShowRate,
                'staff_performance' => $staffPerformance,
                'popular_services' => $popularServices,
            ],
            'message' => '',
        ]);
    }
}
