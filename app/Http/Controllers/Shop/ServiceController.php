<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Shop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ServiceController extends Controller
{
    private function getOwnerShop(): Shop
    {
        return Shop::where('owner_id', auth()->id())->firstOrFail();
    }

    public function index()
    {
        $shop = $this->getOwnerShop();
        $services = $shop->services()->orderBy('sort_order')->get();

        return response()->json(['success' => true, 'data' => $services, 'message' => '']);
    }

    public function store(Request $request)
    {
        $shop = $this->getOwnerShop();

        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'price' => ['required', 'numeric', 'min:0'],
            'duration_min' => ['required', 'integer', 'min:5'],
            'category' => ['nullable', 'string', 'max:100'],
            'gender' => ['in:male,female,both'],
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'data' => null, 'message' => $validator->errors()->first()], 422);
        }

        $service = $shop->services()->create($request->only(['name', 'price', 'duration_min', 'category', 'gender']));

        return response()->json(['success' => true, 'data' => $service, 'message' => 'Hizmet eklendi.'], 201);
    }

    public function update(Request $request, $id)
    {
        $shop = $this->getOwnerShop();
        $service = $shop->services()->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => ['sometimes', 'string', 'max:255'],
            'price' => ['sometimes', 'numeric', 'min:0'],
            'duration_min' => ['sometimes', 'integer', 'min:5'],
            'category' => ['nullable', 'string', 'max:100'],
            'gender' => ['in:male,female,both'],
            'is_active' => ['boolean'],
            'sort_order' => ['integer'],
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'data' => null, 'message' => $validator->errors()->first()], 422);
        }

        $service->update($request->only(['name', 'price', 'duration_min', 'category', 'gender', 'is_active', 'sort_order']));

        return response()->json(['success' => true, 'data' => $service->fresh(), 'message' => 'Hizmet güncellendi.']);
    }

    public function destroy($id)
    {
        $shop = $this->getOwnerShop();
        $service = $shop->services()->findOrFail($id);
        $service->update(['is_active' => false]);

        return response()->json(['success' => true, 'data' => null, 'message' => 'Hizmet pasif yapıldı.']);
    }
}
