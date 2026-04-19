<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shop_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->unique()->constrained('shops')->onDelete('cascade');
            $table->integer('slot_interval')->default(30); // dakika
            $table->integer('min_advance_hours')->default(2);
            $table->integer('max_advance_days')->default(14);
            $table->integer('cancel_hours')->default(2);
            $table->boolean('auto_approve')->default(true);
            $table->boolean('deposit_required')->default(false);
            $table->decimal('deposit_amount', 10, 2)->default(50);
            $table->decimal('deposit_percentage', 4, 2)->nullable();
            $table->integer('max_daily_per_user')->default(1);
            $table->boolean('walkin_enabled')->default(true);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shop_settings');
    }
};
