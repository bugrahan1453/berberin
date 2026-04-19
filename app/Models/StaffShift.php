<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StaffShift extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'staff_id', 'date', 'start_time', 'end_time',
        'break_start', 'break_end', 'is_off',
    ];

    protected $casts = [
        'date' => 'date',
        'is_off' => 'boolean',
    ];

    public function staff()
    {
        return $this->belongsTo(Staff::class);
    }
}
