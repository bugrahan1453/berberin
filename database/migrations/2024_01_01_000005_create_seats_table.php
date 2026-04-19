<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->constrained('shops')->onDelete('cascade');
            $table->string('name', 100);
            $table->foreignId('assigned_staff_id')->nullable()->constrained('staff')->nullOnDelete();
            $table->enum('status', ['empty', 'busy', 'reserved', 'inactive'])->default('empty');
            $table->boolean('is_vip')->default(false);
            $table->unsignedBigInteger('current_appointment_id')->nullable();
            $table->timestamp('busy_since')->nullable();
            $table->timestamp('estimated_free_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seats');
    }
};
