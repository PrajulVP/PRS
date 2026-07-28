<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class LeaveStatusUpdatedNotification extends Notification
{
    use Queueable;

    protected $leave;
    protected $status;
    protected $actor;

    public function __construct($leave, $status, $actor)
    {
        $this->leave = $leave;
        $this->status = $status;
        $this->actor = $actor;
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $statusStr = ucfirst(str_replace('_', ' ', $this->status));
        $startDate = \Carbon\Carbon::parse($this->leave->start_date)->format('Y-m-d');
        return [
            'leave_id' => $this->leave->id,
            'message' => "Your leave request for {$startDate} has been {$statusStr} by {$this->actor->name}.",
            'action_url' => null,
            'type' => 'leave_status'
        ];
    }
}
