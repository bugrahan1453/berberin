<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Review;
use App\Models\Shop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ReviewController extends Controller
{
    private function getOwnerShop(): Shop
    {
        return Shop::where('owner_id', auth()->id())->firstOrFail();
    }

    // Dükkan sahibi yorumları listeler
    public function shopIndex()
    {
        $shop = $this->getOwnerShop();
        $reviews = Review::with('user:id,name,avatar')
            ->where('shop_id', $shop->id)
            ->latest()
            ->paginate(20);

        return response()->json(['success' => true, 'data' => $reviews, 'message' => '']);
    }

    // Dükkan sahibi yoruma cevap verir
    public function reply(Request $request, $id)
    {
        $shop = $this->getOwnerShop();
        $review = Review::where('shop_id', $shop->id)->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'reply' => ['required', 'string', 'max:1000'],
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'data' => null, 'message' => $validator->errors()->first()], 422);
        }

        $review->update([
            'reply' => $request->reply,
            'replied_at' => now(),
        ]);

        return response()->json(['success' => true, 'data' => $review->fresh(), 'message' => 'Cevap kaydedildi.']);
    }

    // Müşteri yorum yapar
    public function storeCustomer(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'appointment_id' => ['required', 'exists:appointments,id'],
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string', 'max:1000'],
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'data' => null, 'message' => $validator->errors()->first()], 422);
        }

        $appointment = Appointment::where('user_id', auth()->id())
            ->where('status', 'completed')
            ->findOrFail($request->appointment_id);

        // Daha önce yorum yapıldıysa güncelle
        $review = Review::updateOrCreate(
            ['appointment_id' => $appointment->id, 'user_id' => auth()->id()],
            [
                'shop_id' => $appointment->shop_id,
                'rating' => $request->rating,
                'comment' => $request->comment,
            ]
        );

        // Ortalama puanı güncelle
        $shop = $appointment->shop;
        $avgRating = Review::where('shop_id', $shop->id)->avg('rating');
        $totalReviews = Review::where('shop_id', $shop->id)->count();
        $shop->update(['avg_rating' => round($avgRating, 1), 'total_reviews' => $totalReviews]);

        // Güven skoru +2
        auth()->user()->increment('trust_score', 2);

        return response()->json(['success' => true, 'data' => $review, 'message' => 'Yorumunuz alındı.'], 201);
    }
}
