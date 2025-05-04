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
            // Subcategory logic removed
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

    public function office()
    {
        return $this->belongsTo(Office::class);
    }

    public function issues()
    {
        return $this->hasMany(Issue::class);
    }
}
