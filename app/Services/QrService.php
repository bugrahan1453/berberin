<?php

namespace App\Services;

use App\Models\Shop;

class QrService
{
    /**
     * Dükkan için QR kodu URL'i üretir.
     * Gerçek QR üretimi için bir QR kütüphanesi kullanılabilir (FAZ 4'te entegre edilecek).
     */
    public function generate(Shop $shop): string
    {
        $shopUrl = url('/s/' . $shop->slug);

        // Google Charts API ile QR URL (harici bağımlılık yok)
        $encodedUrl = urlencode($shopUrl);
        return "https://chart.googleapis.com/chart?cht=qr&chs=300x300&chl={$encodedUrl}&choe=UTF-8";
    }
}
