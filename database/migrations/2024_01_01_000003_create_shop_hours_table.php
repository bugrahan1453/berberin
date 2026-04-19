<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shop_hours', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->constrained('shops')->onDelete('cascade');
            $table->tinyInteger('day_of_week'); // 0=Pzt, 6=Paz
            $table->time('open_time')->nullable();
            $table->time('close_time')->nullable();
            $table->time('break_start')->nullable();
            $table->time('break_end')->nullable();
            $table->boolean('is_closed')->default(false);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shop_hours');
    }
};
