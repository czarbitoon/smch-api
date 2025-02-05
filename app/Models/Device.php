<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Device extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'office_id',
        'serial_number',
        'status',
        'notes'
    ];

    public function office()
    {
        return $this->belongsTo(Office::class);
    }
}
