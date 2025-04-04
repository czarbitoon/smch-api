<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;

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

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($device) {
            if ($device->device_subcategory_id) {
                $subcategory = DeviceSubcategory::with(['device_type.device_category'])->find($device->device_subcategory_id);

                if (!$subcategory) {
                    logger()->error('Device subcategory not found', ['subcategory_id' => $device->device_subcategory_id]);
                    throw new \Exception('Device subcategory not found');
                }

                if (!$subcategory->device_type) {
                    logger()->error('Device type not found for subcategory', [
                        'subcategory_id' => $device->device_subcategory_id,
                        'subcategory_name' => $subcategory->name
                    ]);
                    throw new \Exception('Device type not found for subcategory');
                }

                $device->device_type_id = $subcategory->device_type_id;
                $device->device_category_id = $subcategory->device_type->device_category_id;
            }
        });
    }

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

    public function issues()
    {
        return $this->hasMany(Issue::class);
    }
}
