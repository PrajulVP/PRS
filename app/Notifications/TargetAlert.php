<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TargetAlert extends Notification
{
    use Queueable;

    protected $message;
    protected $percent;
    protected $orderCode;

    /**
     * Create a new notification instance.
     */
    public function __construct($message, $percent = null, $orderCode = null)
    {
        $this->message = $message;
        $this->percent = $percent;
        $this->orderCode = $orderCode;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'target_alert',
            'message' => $this->message,
            'percent' => $this->percent,
            'order_code' => $this->orderCode,
            'action_url' => url('/admin/reports/targets')
        ];
    }
}
