<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Shop extends Model
{
    use HasFactory;

    protected $fillable = [
        'owner_id', 'name', 'slug', 'description', 'address',
        'city', 'district', 'lat', 'lng', 'phone', 'email',
        'logo', 'cover_image', 'instagram', 'tiktok',
        'is_active', 'is_full', 'is_verified', 'total_seats',
        'gender_filter', 'avg_rating', 'total_reviews',
        'subscription_plan', 'subscription_expires_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_full' => 'boolean',
        'is_verified' => 'boolean',
        'lat' => 'float',
        'lng' => 'float',
        'avg_rating' => 'float',
        'subscription_expires_at' => 'datetime',
    ];

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function hours()
    {
        return $this->hasMany(ShopHour::class);
    }

    public function seats()
    {
        return $this->hasMany(Seat::class);
    }

    public function staff()
    {
        return $this->hasMany(Staff::class);
    }

    public function services()
    {
        return $this->hasMany(Service::class);
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function gallery()
    {
        return $this->hasMany(ShopGallery::class);
    }

    public function settings()
    {
        return $this->hasOne(ShopSetting::class);
    }

    public function notificationSettings()
    {
        return $this->hasOne(NotificationSetting::class);
    }

    public function queueStatus()
    {
        return $this->hasOne(QueueStatus::class);
    }
}
