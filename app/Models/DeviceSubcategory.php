<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeviceSubcategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'device_type_id',
    ];

    public function devices()
    {
        return $this->hasMany(Device::class);
    }

    public function device_type()
    {
        return $this->belongsTo(DeviceType::class);
    }
}
