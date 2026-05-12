<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    use HasFactory;

    // Priority constants
    public const PRIORITY_LOW = 'low';
    public const PRIORITY_MEDIUM = 'medium';
    public const PRIORITY_HIGH = 'high';

    public static array $allowedPriorities = [
        self::PRIORITY_LOW,
        self::PRIORITY_MEDIUM,
        self::PRIORITY_HIGH,
    ];

    // Status constants
    public const STATUS_OPEN = 'open';
    public const STATUS_IN_PROGRESS = 'in-progress';
    public const STATUS_RESOLVED = 'resolved';
    public const STATUS_CLOSED = 'closed';

    public static array $allowedStatuses = [
        self::STATUS_OPEN,
        self::STATUS_IN_PROGRESS,
        self::STATUS_RESOLVED,
        self::STATUS_CLOSED,
    ];

    protected $fillable = [
        'subject',
        'description',
        'user_id',          // Reporter
        'device_id',
        'office_id',
        'assigned_to',      // Assigned technician
        'status',
        'priority',
        'resolution_notes',
        'started_at',
        'resolved_at',
        'image',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'resolved_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the user who reported the ticket
     */
    public function reporter()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the device associated with the ticket
     */
    public function device()
    {
        return $this->belongsTo(Device::class);
    }

    /**
     * Get the office where the ticket originated
     */
    public function office()
    {
        return $this->belongsTo(Office::class);
    }

    /**
     * Get the technician assigned to this ticket
     */
    public function assignedTechnician()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * Check if ticket is open
     */
    public function isOpen(): bool
    {
        return $this->status === self::STATUS_OPEN;
    }

    /**
     * Check if ticket is in progress
     */
    public function isInProgress(): bool
    {
        return $this->status === self::STATUS_IN_PROGRESS;
    }

    /**
     * Check if ticket is resolved
     */
    public function isResolved(): bool
    {
        return $this->status === self::STATUS_RESOLVED;
    }

    /**
     * Check if ticket is closed
     */
    public function isClosed(): bool
    {
        return $this->status === self::STATUS_CLOSED;
    }
}
