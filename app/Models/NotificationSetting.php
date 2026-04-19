<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationSetting extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'shop_id', 'morning_summary_enabled', 'morning_summary_time',
        'evening_summary_enabled', 'weekly_report_enabled', 'sms_enabled',
        'reminder_hours', 'no_show_auto_mark_minutes',
        'review_notification_enabled', 'campaign_notification_enabled',
    ];

    protected $casts = [
        'morning_summary_enabled' => 'boolean',
        'evening_summary_enabled' => 'boolean',
        'weekly_report_enabled' => 'boolean',
        'sms_enabled' => 'boolean',
        'review_notification_enabled' => 'boolean',
        'campaign_notification_enabled' => 'boolean',
    ];

    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }
}
