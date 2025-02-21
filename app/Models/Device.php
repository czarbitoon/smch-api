<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Device extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'device_subcategory_id',
        'office_id',
        'serial_number',
        'model_number',
        'manufacturer',
        'status'
    ];

    public function subcategory()
    {
        return $this->belongsTo(DeviceSubcategory::class);
    }

    public function office()
    {
        return $this->belongsTo(Office::class);
    }
}
