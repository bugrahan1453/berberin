<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('shop_id')->constrained('shops');
            $table->foreignId('staff_id')->nullable()->constrained('staff')->nullOnDelete();
            $table->foreignId('service_id')->constrained('services');
            $table->foreignId('seat_id')->nullable()->constrained('seats')->nullOnDelete();
            $table->date('date');
            $table->time('time');
            $table->time('end_time');
            $table->enum('status', ['pending', 'confirmed', 'in_progress', 'completed', 'cancelled', 'no_show'])->default('pending');
            $table->decimal('price', 10, 2);
            $table->enum('source', ['app', 'web', 'walkin', 'phone', 'qr'])->default('app');
            $table->string('customer_name', 255)->nullable(); // walk-in/telefon için
            $table->string('customer_phone', 20)->nullable(); // walk-in/telefon için
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->enum('cancelled_by', ['customer', 'shop', 'system'])->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
