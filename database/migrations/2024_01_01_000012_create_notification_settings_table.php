<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notification_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->constrained('shops')->onDelete('cascade');
            $table->boolean('morning_summary_enabled')->default(true);
            $table->time('morning_summary_time')->default('08:00:00');
            $table->boolean('evening_summary_enabled')->default(true);
            $table->boolean('weekly_report_enabled')->default(true);
            $table->boolean('sms_enabled')->default(true);
            $table->integer('reminder_hours')->default(2);
            $table->integer('no_show_auto_mark_minutes')->default(30);
            $table->boolean('review_notification_enabled')->default(true);
            $table->boolean('campaign_notification_enabled')->default(true);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_settings');
    }
};
