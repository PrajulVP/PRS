<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderActionRequired extends Notification
{
    use Queueable;

    protected $order;
    protected $message;
    protected $actionUrl;

    /**
     * Create a new notification instance.
     */
    public function __construct($order, $message, $actionUrl = null)
    {
        $this->order = $order;
        $this->message = $message;
        $this->actionUrl = $actionUrl;
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
            'order_id' => $this->order->id,
            'order_code' => $this->order->order_code,
            'message' => $this->message,
            'action_url' => $this->actionUrl,
            'type' => 'order_action'
        ];
    }
}
