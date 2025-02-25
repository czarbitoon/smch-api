<?php

namespace App\Listeners;

use App\Events\ReportSubmitted;
use App\Models\Notification;
use App\Models\User;

class CreateReportNotification
{
    public function handle(ReportSubmitted $event)
    {   
        $report = $event->report;
        $device = $report->device;

        // Create notification for the report submitter (regular user)
        if ($report->user_id) {
            Notification::create([
                'title' => 'Your Device Report Has Been Submitted',
                'message' => "Your report for device {$device->name} has been submitted successfully",
                'user_id' => $report->user_id,
                'report_id' => $report->id,
                'read' => false
            ]);
        }

        // Create notification for admin users
        $adminUsers = User::where('type', '>=', 2)->get();
        foreach ($adminUsers as $admin) {
            Notification::create([
                'title' => 'New Device Report Submitted',
                'message' => "A new report has been submitted for device {$device->name}",
                'user_id' => $admin->id,
                'report_id' => $report->id,
                'read' => false
            ]);
        }

        // Create notification for staff users in the same office
        if ($device && $device->office_id) {
            $staffUsers = User::where('type', 1)
                ->where('office_id', $device->office_id)
                ->get();

            foreach ($staffUsers as $staff) {
                Notification::create([
                    'title' => 'New Device Report in Your Office',
                    'message' => "A new report has been submitted for device {$device->name} in your office",
                    'user_id' => $staff->id,
                    'report_id' => $report->id,
                    'read' => false
                ]);
            }
        }
    }
}
