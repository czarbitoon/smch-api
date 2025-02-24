<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeviceCategory extends Model
{
    protected $fillable = ['name'];

    public function deviceTypes()
    {
        return $this->hasMany(DeviceType::class);
    }
}