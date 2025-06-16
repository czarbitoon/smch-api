<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\DatabaseMessage;
use Illuminate\Notifications\Messages\MailMessage;

class DeviceReportedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $report;
    public $device;
    public $actor;

    public function __construct($report, $device, $actor)
    {
        $this->report = $report;
        $this->device = $device;
        $this->actor = $actor;
    }

    public function via($notifiable)
    {
        return ['database', 'broadcast'];
    }

    public function toArray($notifiable)
    {
        return [
            'report_id' => $this->report->id,
            'device_id' => $this->device->id,
            'device_name' => $this->device->name ?? $this->device->label ?? 'Device',
            'actor_id' => $this->actor->id,
            'actor_name' => $this->actor->name,
            'message' => $this->actor->name . ' reported device: ' . ($this->device->name ?? $this->device->label ?? 'Device'),
        ];
    }

    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage($this->toArray($notifiable));
    }

    public function toDatabase($notifiable)
    {
        return new DatabaseMessage($this->toArray($notifiable));
    }
}
