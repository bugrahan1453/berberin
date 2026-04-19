<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\NotificationSetting;
use App\Models\Shop;
use App\Models\ShopSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SettingsController extends Controller
{
    private function getOwnerShop(): Shop
    {
        return Shop::where('owner_id', auth()->id())->firstOrFail();
    }

    public function show()
    {
        $shop = $this->getOwnerShop();
        $settings = $shop->settings ?? ShopSetting::create(['shop_id' => $shop->id]);

        return response()->json(['success' => true, 'data' => $settings, 'message' => '']);
    }

    public function update(Request $request)
    {
        $shop = $this->getOwnerShop();

        $validator = Validator::make($request->all(), [
            'slot_interval' => ['sometimes', 'integer', 'in:15,30,45,60'],
            'min_advance_hours' => ['sometimes', 'integer', 'min:0'],
            'max_advance_days' => ['sometimes', 'integer', 'min:1', 'max:90'],
            'cancel_hours' => ['sometimes', 'integer', 'min:0'],
            'auto_approve' => ['sometimes', 'boolean'],
            'deposit_required' => ['sometimes', 'boolean'],
            'deposit_amount' => ['sometimes', 'numeric', 'min:0'],
            'deposit_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'max_daily_per_user' => ['sometimes', 'integer', 'min:1'],
            'walkin_enabled' => ['sometimes', 'boolean'],
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'data' => null, 'message' => $validator->errors()->first()], 422);
        }

        $settings = ShopSetting::updateOrCreate(
            ['shop_id' => $shop->id],
            $request->only([
                'slot_interval', 'min_advance_hours', 'max_advance_days',
                'cancel_hours', 'auto_approve', 'deposit_required',
                'deposit_amount', 'deposit_percentage', 'max_daily_per_user', 'walkin_enabled',
            ])
        );

        return response()->json(['success' => true, 'data' => $settings, 'message' => 'Ayarlar güncellendi.']);
    }

    public function notificationShow()
    {
        $shop = $this->getOwnerShop();
        $settings = $shop->notificationSettings ?? NotificationSetting::create(['shop_id' => $shop->id]);

        return response()->json(['success' => true, 'data' => $settings, 'message' => '']);
    }

    public function notificationUpdate(Request $request)
    {
        $shop = $this->getOwnerShop();

        $validator = Validator::make($request->all(), [
            'morning_summary_enabled' => ['sometimes', 'boolean'],
            'morning_summary_time' => ['sometimes', 'date_format:H:i'],
            'evening_summary_enabled' => ['sometimes', 'boolean'],
            'weekly_report_enabled' => ['sometimes', 'boolean'],
            'sms_enabled' => ['sometimes', 'boolean'],
            'reminder_hours' => ['sometimes', 'integer', 'min:1', 'max:4'],
            'no_show_auto_mark_minutes' => ['sometimes', 'integer', 'min:15', 'max:60'],
            'review_notification_enabled' => ['sometimes', 'boolean'],
            'campaign_notification_enabled' => ['sometimes', 'boolean'],
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'data' => null, 'message' => $validator->errors()->first()], 422);
        }

        $settings = NotificationSetting::updateOrCreate(
            ['shop_id' => $shop->id],
            $request->only([
                'morning_summary_enabled', 'morning_summary_time', 'evening_summary_enabled',
                'weekly_report_enabled', 'sms_enabled', 'reminder_hours',
                'no_show_auto_mark_minutes', 'review_notification_enabled', 'campaign_notification_enabled',
            ])
        );

        return response()->json(['success' => true, 'data' => $settings, 'message' => 'Bildirim ayarları güncellendi.']);
    }
}
