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
    protected $orderType;

    /**
     * Create a new notification instance.
     */
    public function __construct($order, $message, $actionUrl = null, $orderType = 'retailer_order')
    {
        $this->order = $order;
        $this->message = $message;
        $this->actionUrl = $actionUrl;
        $this->orderType = $orderType;
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
            'order_type' => $this->orderType,
            'message' => $this->message,
            'action_url' => $this->actionUrl,
            'type' => 'order_action'
        ];
    }
}
