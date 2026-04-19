<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Favorite;
use App\Models\Shop;

class FavoriteController extends Controller
{
    public function index()
    {
        $favorites = auth()->user()->favoriteShops()
            ->select('shops.id', 'shops.name', 'shops.slug', 'shops.logo', 'shops.avg_rating', 'shops.city', 'shops.is_full')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $favorites,
            'message' => '',
        ]);
    }

    public function store($shopId)
    {
        Shop::findOrFail($shopId);

        Favorite::firstOrCreate([
            'user_id' => auth()->id(),
            'shop_id' => $shopId,
        ]);

        return response()->json([
            'success' => true,
            'data' => null,
            'message' => 'Favorilere eklendi.',
        ]);
    }

    public function destroy($shopId)
    {
        Favorite::where('user_id', auth()->id())
            ->where('shop_id', $shopId)
            ->delete();

        return response()->json([
            'success' => true,
            'data' => null,
            'message' => 'Favorilerden kaldırıldı.',
        ]);
    }
}
