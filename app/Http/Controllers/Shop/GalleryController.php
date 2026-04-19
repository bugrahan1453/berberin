<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Shop;
use App\Models\ShopGallery;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class GalleryController extends Controller
{
    private function getOwnerShop(): Shop
    {
        return Shop::where('owner_id', auth()->id())->firstOrFail();
    }

    public function index()
    {
        $shop = $this->getOwnerShop();
        $gallery = $shop->gallery()->orderBy('sort_order')->get();

        return response()->json(['success' => true, 'data' => $gallery, 'message' => '']);
    }

    public function store(Request $request)
    {
        $shop = $this->getOwnerShop();

        $validator = Validator::make($request->all(), [
            'image_url' => ['required', 'string', 'max:500'],
            'caption' => ['nullable', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer'],
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'data' => null, 'message' => $validator->errors()->first()], 422);
        }

        $item = $shop->gallery()->create($request->only(['image_url', 'caption', 'sort_order']));

        return response()->json(['success' => true, 'data' => $item, 'message' => 'Fotoğraf eklendi.'], 201);
    }

    public function destroy($id)
    {
        $shop = $this->getOwnerShop();
        ShopGallery::where('shop_id', $shop->id)->findOrFail($id)->delete();

        return response()->json(['success' => true, 'data' => null, 'message' => 'Fotoğraf silindi.']);
    }
}
