<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Seat;
use App\Models\Shop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SeatController extends Controller
{
    private function getOwnerShop(): Shop
    {
        return Shop::where('owner_id', auth()->id())->firstOrFail();
    }

    public function index()
    {
        $shop = $this->getOwnerShop();
        $seats = $shop->seats()->with(['assignedStaff:id,name,avatar', 'currentAppointment.user:id,name'])->get();

        return response()->json(['success' => true, 'data' => $seats, 'message' => '']);
    }

    public function store(Request $request)
    {
        $shop = $this->getOwnerShop();

        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:100'],
            'assigned_staff_id' => ['nullable', 'exists:staff,id'],
            'is_vip' => ['boolean'],
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'data' => null, 'message' => $validator->errors()->first()], 422);
        }

        $seat = $shop->seats()->create($request->only(['name', 'assigned_staff_id', 'is_vip']));

        // Dükkanın toplam koltuk sayısını güncelle
        $shop->increment('total_seats');

        return response()->json(['success' => true, 'data' => $seat, 'message' => 'Koltuk eklendi.'], 201);
    }

    public function update(Request $request, $id)
    {
        $shop = $this->getOwnerShop();
        $seat = $shop->seats()->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => ['sometimes', 'string', 'max:100'],
            'assigned_staff_id' => ['nullable', 'exists:staff,id'],
            'is_vip' => ['boolean'],
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'data' => null, 'message' => $validator->errors()->first()], 422);
        }

        $seat->update($request->only(['name', 'assigned_staff_id', 'is_vip']));

        return response()->json(['success' => true, 'data' => $seat->fresh(), 'message' => 'Koltuk güncellendi.']);
    }

    public function updateStatus(Request $request, $id)
    {
        $shop = $this->getOwnerShop();
        $seat = $shop->seats()->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'status' => ['required', 'in:empty,busy,reserved,inactive'],
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'data' => null, 'message' => $validator->errors()->first()], 422);
        }

        $updateData = ['status' => $request->status];

        if ($request->status === 'empty') {
            $updateData['current_appointment_id'] = null;
            $updateData['busy_since'] = null;
            $updateData['estimated_free_at'] = null;
        } elseif ($request->status === 'busy') {
            $updateData['busy_since'] = now();
        }

        $seat->update($updateData);

        // Dükkanın doluluk durumunu güncelle
        $totalActive = $shop->seats()->whereNotIn('status', ['inactive'])->count();
        $busyCount = $shop->seats()->whereIn('status', ['busy', 'reserved'])->count();
        $shop->update(['is_full' => $totalActive > 0 && $busyCount >= $totalActive]);

        return response()->json(['success' => true, 'data' => $seat->fresh(), 'message' => 'Koltuk durumu güncellendi.']);
    }

    public function destroy($id)
    {
        $shop = $this->getOwnerShop();
        $seat = $shop->seats()->findOrFail($id);
        $seat->delete();

        $shop->decrement('total_seats');

        return response()->json(['success' => true, 'data' => null, 'message' => 'Koltuk silindi.']);
    }
}
