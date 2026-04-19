<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Seat extends Model
{
    protected $fillable = [
        'shop_id', 'name', 'assigned_staff_id', 'status',
        'is_vip', 'current_appointment_id', 'busy_since', 'estimated_free_at',
    ];

    protected $casts = [
        'is_vip' => 'boolean',
        'busy_since' => 'datetime',
        'estimated_free_at' => 'datetime',
    ];

    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }

    public function assignedStaff()
    {
        return $this->belongsTo(Staff::class, 'assigned_staff_id');
    }

    public function currentAppointment()
    {
        return $this->belongsTo(Appointment::class, 'current_appointment_id');
    }
}
