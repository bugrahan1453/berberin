<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Shop;
use App\Models\Service;
use App\Services\SlotService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BookingController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->status ?? null;

        $query = Appointment::with(['shop:id,name,slug,logo,address', 'service:id,name,price', 'staff:id,name,avatar'])
            ->where('user_id', auth()->id())
            ->orderByDesc('date');

        if ($status === 'upcoming') {
            $query->whereIn('status', ['pending', 'confirmed'])->where('date', '>=', now()->toDateString());
        } elseif ($status === 'completed') {
            $query->where('status', 'completed');
        } elseif ($status === 'cancelled') {
            $query->whereIn('status', ['cancelled', 'no_show']);
        }

        return response()->json([
            'success' => true,
            'data' => $query->paginate(10),
            'message' => '',
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'shop_id' => ['required', 'exists:shops,id'],
            'service_id' => ['required', 'exists:services,id'],
            'staff_id' => ['nullable', 'exists:staff,id'],
            'date' => ['required', 'date', 'after_or_equal:today'],
            'time' => ['required', 'date_format:H:i'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $shop = Shop::with('settings')->findOrFail($request->shop_id);
        $service = Service::findOrFail($request->service_id);
        $user = auth()->user();

        // Güven skoru / ban kontrolü
        $noShowLog = \App\Models\NoShowLog::where('user_id', $user->id)
            ->whereNotNull('penalty_until')
            ->where('penalty_until', '>', now())
            ->first();

        if ($noShowLog) {
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => 'No-show cezası nedeniyle randevu alamazsınız. Yasak bitiş: ' . $noShowLog->penalty_until->format('d.m.Y H:i'),
            ], 403);
        }

        $endTime = date('H:i', strtotime($request->time) + ($service->duration_min * 60));

        $appointment = Appointment::create([
            'user_id' => $user->id,
            'shop_id' => $shop->id,
            'service_id' => $service->id,
            'staff_id' => $request->staff_id,
            'date' => $request->date,
            'time' => $request->time,
            'end_time' => $endTime,
            'price' => $service->price,
            'source' => 'app',
            'status' => $shop->settings?->auto_approve ? 'confirmed' : 'pending',
        ]);

        return response()->json([
            'success' => true,
            'data' => $appointment->load(['shop:id,name,address', 'service:id,name,price']),
            'message' => 'Randevunuz oluşturuldu.',
        ], 201);
    }

    public function show($id)
    {
        $appointment = Appointment::with(['shop', 'service', 'staff', 'review'])
            ->where('user_id', auth()->id())
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $appointment,
            'message' => '',
        ]);
    }

    public function cancel($id)
    {
        $appointment = Appointment::where('user_id', auth()->id())->findOrFail($id);

        if (!in_array($appointment->status, ['pending', 'confirmed'])) {
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => 'Bu randevu iptal edilemez.',
            ], 422);
        }

        $appointment->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'cancelled_by' => 'customer',
        ]);

        return response()->json([
            'success' => true,
            'data' => $appointment,
            'message' => 'Randevunuz iptal edildi.',
        ]);
    }

    public function confirm($id)
    {
        $appointment = Appointment::where('user_id', auth()->id())->findOrFail($id);

        if ($appointment->status !== 'confirmed') {
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => 'Bu randevu onaylanamaz.',
            ], 422);
        }

        $appointment->update(['confirmed_at' => now()]);

        return response()->json([
            'success' => true,
            'data' => $appointment,
            'message' => 'Randevunuz onaylandı.',
        ]);
    }
}
