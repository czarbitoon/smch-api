<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'report_id',
        'assigned_to',
        'device_id',
        'job_type',
        'description',
        'priority',
        'status',
        'work_performed',
        'parts_used',
        'labor_hours',
        'started_at',
        'completed_at'
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime'
    ];

    public function report()
    {
        return $this->belongsTo(Report::class);
    }

    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function device()
    {
        return $this->belongsTo(Device::class);
    }
}