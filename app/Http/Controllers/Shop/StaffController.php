<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Shop;
use App\Models\StaffShift;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class StaffController extends Controller
{
    private function getOwnerShop(): Shop
    {
        return Shop::where('owner_id', auth()->id())->firstOrFail();
    }

    public function index()
    {
        $shop = $this->getOwnerShop();
        $staff = $shop->staff()->get();

        return response()->json(['success' => true, 'data' => $staff, 'message' => '']);
    }

    public function store(Request $request)
    {
        $shop = $this->getOwnerShop();

        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'role' => ['in:owner,manager,staff'],
            'specialties' => ['nullable', 'array'],
            'commission_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'data' => null, 'message' => $validator->errors()->first()], 422);
        }

        $staff = $shop->staff()->create($request->only(['name', 'phone', 'avatar', 'role', 'specialties', 'commission_rate']));

        return response()->json(['success' => true, 'data' => $staff, 'message' => 'Personel eklendi.'], 201);
    }

    public function update(Request $request, $id)
    {
        $shop = $this->getOwnerShop();
        $staff = $shop->staff()->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => ['sometimes', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'role' => ['in:owner,manager,staff'],
            'specialties' => ['nullable', 'array'],
            'commission_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'is_active' => ['boolean'],
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'data' => null, 'message' => $validator->errors()->first()], 422);
        }

        $staff->update($request->only(['name', 'phone', 'avatar', 'role', 'specialties', 'commission_rate', 'is_active']));

        return response()->json(['success' => true, 'data' => $staff->fresh(), 'message' => 'Personel güncellendi.']);
    }

    public function destroy($id)
    {
        $shop = $this->getOwnerShop();
        $staff = $shop->staff()->findOrFail($id);
        $staff->update(['is_active' => false]); // Soft disable

        return response()->json(['success' => true, 'data' => null, 'message' => 'Personel pasif yapıldı.']);
    }

    public function shifts($id)
    {
        $shop = $this->getOwnerShop();
        $staff = $shop->staff()->findOrFail($id);

        $shifts = $staff->shifts()
            ->where('date', '>=', now()->startOfWeek()->toDateString())
            ->where('date', '<=', now()->endOfWeek()->addWeeks(2)->toDateString())
            ->orderBy('date')
            ->get();

        return response()->json(['success' => true, 'data' => $shifts, 'message' => '']);
    }

    public function storeShift(Request $request, $id)
    {
        $shop = $this->getOwnerShop();
        $staff = $shop->staff()->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'date' => ['required', 'date'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'break_start' => ['nullable', 'date_format:H:i'],
            'break_end' => ['nullable', 'date_format:H:i'],
            'is_off' => ['boolean'],
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'data' => null, 'message' => $validator->errors()->first()], 422);
        }

        $shift = StaffShift::updateOrCreate(
            ['staff_id' => $staff->id, 'date' => $request->date],
            $request->only(['start_time', 'end_time', 'break_start', 'break_end', 'is_off'])
        );

        return response()->json(['success' => true, 'data' => $shift, 'message' => 'Vardiya kaydedildi.'], 201);
    }
}
