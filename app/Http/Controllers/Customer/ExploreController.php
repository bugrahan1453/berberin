<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Shop;
use App\Services\SlotService;
use Illuminate\Http\Request;

class ExploreController extends Controller
{
    public function index(Request $request)
    {
        $query = Shop::with(['settings', 'queueStatus'])
            ->where('is_active', true);

        // Cinsiyet filtresi
        if ($request->gender) {
            $query->where(function ($q) use ($request) {
                $q->where('gender_filter', $request->gender)
                  ->orWhere('gender_filter', 'both');
            });
        }

        // Şehir/ilçe filtresi
        if ($request->city) {
            $query->where('city', $request->city);
        }

        // Arama
        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'LIKE', '%' . $request->search . '%')
                  ->orWhere('address', 'LIKE', '%' . $request->search . '%');
            });
        }

        // Konum bazlı sıralama (Haversine formülü)
        if ($request->lat && $request->lng) {
            $lat = (float) $request->lat;
            $lng = (float) $request->lng;
            $query->selectRaw(
                "*, (6371 * acos(cos(radians(?)) * cos(radians(lat)) * cos(radians(lng) - radians(?)) + sin(radians(?)) * sin(radians(lat)))) AS distance",
                [$lat, $lng, $lat]
            )->orderBy('distance');
        } else {
            $query->orderByDesc('avg_rating');
        }

        $shops = $query->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $shops,
            'message' => '',
        ]);
    }

    public function show($slug)
    {
        $shop = Shop::with(['hours', 'settings', 'queueStatus', 'owner'])
            ->where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'data' => $shop,
            'message' => '',
        ]);
    }

    public function services($id)
    {
        $shop = Shop::findOrFail($id);
        $services = $shop->services()->where('is_active', true)->orderBy('sort_order')->get();

        return response()->json([
            'success' => true,
            'data' => $services,
            'message' => '',
        ]);
    }

    public function staff($id)
    {
        $shop = Shop::findOrFail($id);
        $staff = $shop->staff()->where('is_active', true)->get();

        return response()->json([
            'success' => true,
            'data' => $staff,
            'message' => '',
        ]);
    }

    public function reviews($id)
    {
        $shop = Shop::findOrFail($id);
        $reviews = $shop->reviews()
            ->with('user:id,name,avatar')
            ->latest()
            ->paginate(10);

        return response()->json([
            'success' => true,
            'data' => $reviews,
            'message' => '',
        ]);
    }

    public function gallery($id)
    {
        $shop = Shop::findOrFail($id);
        $gallery = $shop->gallery()->orderBy('sort_order')->get();

        return response()->json([
            'success' => true,
            'data' => $gallery,
            'message' => '',
        ]);
    }

    public function availableSlots(Request $request, $id)
    {
        $request->validate([
            'date' => ['required', 'date'],
            'service_id' => ['required', 'integer'],
        ]);

        $shop = Shop::with(['settings', 'hours', 'staff'])->findOrFail($id);
        $slots = app(SlotService::class)->getAvailableSlots($shop, $request->date, $request->service_id);

        return response()->json([
            'success' => true,
            'data' => $slots,
            'message' => '',
        ]);
    }

    public function queueStatus($id)
    {
        $shop = Shop::findOrFail($id);
        $status = $shop->queueStatus ?? [
            'current_waiting' => 0,
            'avg_wait_minutes' => 0,
            'is_full' => false,
        ];

        return response()->json([
            'success' => true,
            'data' => $status,
            'message' => '',
        ]);
    }
}
