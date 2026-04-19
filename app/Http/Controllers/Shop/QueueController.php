<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\QueueStatus;
use App\Models\Shop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class QueueController extends Controller
{
    private function getOwnerShop(): Shop
    {
        return Shop::where('owner_id', auth()->id())->firstOrFail();
    }

    public function update(Request $request)
    {
        $shop = $this->getOwnerShop();

        $validator = Validator::make($request->all(), [
            'current_waiting' => ['sometimes', 'integer', 'min:0'],
            'avg_wait_minutes' => ['sometimes', 'integer', 'min:0'],
            'is_full' => ['sometimes', 'boolean'],
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'data' => null, 'message' => $validator->errors()->first()], 422);
        }

        $queueStatus = QueueStatus::updateOrCreate(
            ['shop_id' => $shop->id],
            array_merge($request->only(['current_waiting', 'avg_wait_minutes', 'is_full']), ['updated_at' => now()])
        );

        // Dükkanın is_full alanını da güncelle
        if ($request->has('is_full')) {
            $shop->update(['is_full' => $request->is_full]);
        }

        return response()->json(['success' => true, 'data' => $queueStatus, 'message' => 'Sıra durumu güncellendi.']);
    }
}
