<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('appointment_id')->constrained('appointments');
            $table->decimal('amount', 10, 2);
            $table->decimal('deposit_amount', 10, 2)->default(0);
            $table->enum('method', ['cash', 'card', 'online'])->default('cash');
            $table->enum('status', ['pending', 'paid', 'refunded', 'forfeited'])->default('pending');
            $table->string('iyzico_ref', 255)->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
