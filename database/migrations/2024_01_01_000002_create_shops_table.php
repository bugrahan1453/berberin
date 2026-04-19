<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shops', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_id')->constrained('users');
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->text('address');
            $table->string('city', 100)->nullable();
            $table->string('district', 100)->nullable();
            $table->decimal('lat', 10, 8);
            $table->decimal('lng', 11, 8);
            $table->string('phone', 20)->nullable();
            $table->string('email', 255)->nullable();
            $table->string('logo', 500)->nullable();
            $table->string('cover_image', 500)->nullable();
            $table->string('instagram', 255)->nullable();
            $table->string('tiktok', 255)->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_full')->default(false);
            $table->boolean('is_verified')->default(false);
            $table->integer('total_seats')->default(1);
            $table->enum('gender_filter', ['male', 'female', 'both'])->default('both');
            $table->decimal('avg_rating', 2, 1)->default(0);
            $table->integer('total_reviews')->default(0);
            $table->enum('subscription_plan', ['starter', 'pro', 'premium'])->default('starter');
            $table->timestamp('subscription_expires_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shops');
    }
};
