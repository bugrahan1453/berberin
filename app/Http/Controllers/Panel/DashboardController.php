<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Shop;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $shop = Shop::where('owner_id', auth()->id())->with(['seats.assignedStaff', 'settings'])->firstOrFail();
        $today = Carbon::today()->toDateString();

        $todayAppointments = Appointment::where('shop_id', $shop->id)->where('date', $today)->count();
        $todayRevenue = Appointment::where('shop_id', $shop->id)->where('date', $today)->where('status', 'completed')->sum('price');
        $pendingCount = Appointment::where('shop_id', $shop->id)->where('date', $today)->where('status', 'pending')->count();

        $upcomingAppointments = Appointment::with(['user:id,name,phone', 'service:id,name', 'staff:id,name', 'seat:id,name'])
            ->where('shop_id', $shop->id)
            ->where('date', $today)
            ->whereIn('status', ['pending', 'confirmed', 'in_progress'])
            ->orderBy('time')
            ->get();

        // Son 7 günlük grafik
        $weeklyLabels = [];
        $weeklyAppointments = [];
        $weeklyRevenue = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $weeklyLabels[] = $date->format('d/m');
            $weeklyAppointments[] = Appointment::where('shop_id', $shop->id)->where('date', $date->toDateString())->count();
            $weeklyRevenue[] = Appointment::where('shop_id', $shop->id)->where('date', $date->toDateString())->where('status', 'completed')->sum('price');
        }

        $totalSeats = $shop->seats->whereNotIn('status', ['inactive'])->count();
        $busySeats = $shop->seats->whereIn('status', ['busy', 'reserved'])->count();
        $occupancyRate = $totalSeats > 0 ? round(($busySeats / $totalSeats) * 100) : 0;

        return view('panel.dashboard.index', compact(
            'shop', 'todayAppointments', 'todayRevenue', 'pendingCount',
            'upcomingAppointments', 'weeklyLabels', 'weeklyAppointments',
            'weeklyRevenue', 'occupancyRate'
        ));
    }
}
