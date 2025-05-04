<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeviceType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'device_category_id',
    ];

    public function device_category()
    {
        return $this->belongsTo(DeviceCategory::class);
    }
}
