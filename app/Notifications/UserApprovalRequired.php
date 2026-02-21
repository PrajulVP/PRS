<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class UserApprovalRequired extends Notification
{
    use Queueable;

    protected $userModel;
    protected $message;
    protected $actionUrl;

    public function __construct($userModel, $message, $actionUrl = null)
    {
        $this->userModel = $userModel;
        $this->message = $message;
        $this->actionUrl = $actionUrl;
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'user_id' => $this->userModel->id,
            'message' => $this->message,
            'action_url' => $this->actionUrl,
            'type' => 'user_approval'
        ];
    }
}
