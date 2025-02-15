<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Office extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['name'];
    protected $dates = ['deleted_at'];

    public function devices()
    {
        return $this->hasMany(Device::class);
    }
}
