<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('no_show_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('appointment_id')->constrained('appointments');
            $table->integer('total_count')->default(1);
            $table->enum('penalty_type', ['warning', 'ban_24h', 'ban_7d', 'ban_30d', 'permanent'])->nullable();
            $table->timestamp('penalty_until')->nullable();
            $table->timestamp('created_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('no_show_logs');
    }
};
