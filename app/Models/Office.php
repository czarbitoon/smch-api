<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Office extends Model
{
    protected $fillable = ['name'];
    use SoftDeletes;

    protected $dates = ['deleted_at'];

    public function devices()
    {
        return $this->hasMany(Device::class);
    }
}

