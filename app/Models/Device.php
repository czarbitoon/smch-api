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
        'device_category_id',
        'device_type_id',
        'device_subcategory_id',
        'office_id',
        'serial_number',
        'model_number',
        'manufacturer',
        'status'
    ];

    protected $with = ['category', 'type', 'subcategory', 'office'];

    public function category()
    {
        return $this->belongsTo(DeviceCategory::class, 'device_category_id');
    }

    public function type()
    {
        return $this->belongsTo(DeviceType::class, 'device_type_id');
    }

    public function subcategory()
    {
        return $this->belongsTo(DeviceSubcategory::class, 'device_subcategory_id');
    }

    public function office()
    {
        return $this->belongsTo(Office::class);
    }
}
