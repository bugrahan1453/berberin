<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NoShowLog extends Model
{
    public $timestamps = false;

    const CREATED_AT = 'created_at';
    const UPDATED_AT = null;

    protected $fillable = [
        'user_id', 'appointment_id', 'total_count',
        'penalty_type', 'penalty_until',
    ];

    protected $casts = [
        'penalty_until' => 'datetime',
        'created_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }
}
