<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'status',
        'user_id',  // User who made the report
        'device_id',  // Associated device
        'device_image_url', // Image of the device
        'report_image',  // Image uploaded with the report
        'office_id',  // Originating office
        'resolved_by', // User who resolved the report
        // Add other fields as needed
    ];


    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function device()
    {
        return $this->belongsTo(Device::class, 'device_id');
    }

    public function office()
    {
        return $this->belongsTo(Office::class, 'office_id');
    }

    public function resolvedByUser()
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }
}
