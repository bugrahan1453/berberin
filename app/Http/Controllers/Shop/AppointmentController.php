<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Service;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AppointmentController extends Controller
{
    private function getOwnerShop(): Shop
    {
        return Shop::where('owner_id', auth()->id())->firstOrFail();
    }

    public function index(Request $request)
    {
        $shop = $this->getOwnerShop();

        $query = Appointment::with(['user:id,name,phone', 'service:id,name,price,duration_min', 'staff:id,name', 'seat:id,name'])
            ->where('shop_id', $shop->id);

        if ($request->date) {
            $query->where('date', $request->date);
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        $appointments = $query->orderBy('date')->orderBy('time')->paginate(20);

        return response()->json(['success' => true, 'data' => $appointments, 'message' => '']);
    }

    public function store(Request $request)
    {
        $shop = $this->getOwnerShop();

        $validator = Validator::make($request->all(), [
            'service_id' => ['required', 'exists:services,id'],
            'staff_id' => ['nullable', 'exists:staff,id'],
            'seat_id' => ['nullable', 'exists:seats,id'],
            'date' => ['required', 'date'],
            'time' => ['required', 'date_format:H:i'],
            'source' => ['in:walkin,phone,app,qr'],
            'customer_name' => ['nullable', 'string', 'max:255'],
            'customer_phone' => ['nullable', 'string', 'max:20'],
            'notes' => ['nullable', 'string'],
            // Kayıtlı kullanıcı için
            'user_id' => ['nullable', 'exists:users,id'],
            'phone' => ['nullable', 'string'], // telefon araması ile randevu
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'data' => null, 'message' => $validator->errors()->first()], 422);
        }

        $service = Service::findOrFail($request->service_id);
        $endTime = date('H:i', strtotime($request->time) + ($service->duration_min * 60));

        // Telefonla gelen randevu: kayıtlı kullanıcı ara
        $userId = $request->user_id;
        $customerName = $request->customer_name;
        $customerPhone = $request->customer_phone;

        if ($request->phone && !$userId) {
            $existingUser = User::where('phone', $request->phone)->first();
            if ($existingUser) {
                $userId = $existingUser->id;
                $customerName = $existingUser->name;
                $customerPhone = $existingUser->phone;
            } else {
                $customerPhone = $request->phone;
            }
        }

        $appointment = Appointment::create([
            'shop_id' => $shop->id,
            'user_id' => $userId,
            'service_id' => $service->id,
            'staff_id' => $request->staff_id,
            'seat_id' => $request->seat_id,
            'date' => $request->date,
            'time' => $request->time,
            'end_time' => $endTime,
            'price' => $service->price,
            'source' => $request->source ?? 'walkin',
            'status' => 'confirmed',
            'customer_name' => $customerName,
            'customer_phone' => $customerPhone,
            'notes' => $request->notes,
            'confirmed_at' => now(),
        ]);

        // Walk-in ise koltuğu dolu işaretle
        if ($request->seat_id && $request->source === 'walkin') {
            \App\Models\Seat::find($request->seat_id)?->update([
                'status' => 'busy',
                'current_appointment_id' => $appointment->id,
                'busy_since' => now(),
            ]);
        }

        return response()->json(['success' => true, 'data' => $appointment->load(['service', 'staff', 'seat']), 'message' => 'Randevu oluşturuldu.'], 201);
    }

    public function updateStatus(Request $request, $id)
    {
        $shop = $this->getOwnerShop();
        $appointment = Appointment::where('shop_id', $shop->id)->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'status' => ['required', 'in:confirmed,in_progress,completed,cancelled,no_show'],
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'data' => null, 'message' => $validator->errors()->first()], 422);
        }

        $updateData = ['status' => $request->status];

        switch ($request->status) {
            case 'in_progress':
                $updateData['started_at'] = now();
                // Koltuk dolu yap
                if ($appointment->seat_id) {
                    \App\Models\Seat::find($appointment->seat_id)?->update([
                        'status' => 'busy',
                        'current_appointment_id' => $appointment->id,
                        'busy_since' => now(),
                    ]);
                }
                break;

            case 'completed':
                $updateData['completed_at'] = now();
                // Koltuğu boşalt
                if ($appointment->seat_id) {
                    \App\Models\Seat::find($appointment->seat_id)?->update([
                        'status' => 'empty',
                        'current_appointment_id' => null,
                        'busy_since' => null,
                    ]);
                }
                break;

            case 'cancelled':
                $updateData['cancelled_at'] = now();
                $updateData['cancelled_by'] = 'shop';
                if ($appointment->seat_id) {
                    \App\Models\Seat::find($appointment->seat_id)?->update(['status' => 'empty']);
                }
                break;
        }

        $appointment->update($updateData);

        return response()->json(['success' => true, 'data' => $appointment->fresh(), 'message' => 'Randevu durumu güncellendi.']);
    }
}
