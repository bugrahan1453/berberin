<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Staff extends Model
{
    use HasFactory;

    protected $fillable = [
        'shop_id', 'user_id', 'name', 'phone', 'avatar',
        'role', 'specialties', 'commission_rate', 'is_active',
    ];

    protected $casts = [
        'specialties' => 'array',
        'is_active' => 'boolean',
        'commission_rate' => 'float',
    ];

    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function shifts()
    {
        return $this->hasMany(StaffShift::class);
    }

    public function seats()
    {
        return $this->hasMany(Seat::class, 'assigned_staff_id');
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }
}
