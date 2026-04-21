<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class VisitReminder extends Notification
{
    use Queueable;

    protected $message;
    protected $shopName;
    protected $address;

    /**
     * Create a new notification instance.
     */
    public function __construct($message, $shopName = null, $address = null)
    {
        $this->message = $message;
        $this->shopName = $shopName;
        $this->address = $address;
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
            'type' => 'visit_reminder',
            'message' => $this->message,
            'shop_name' => $this->shopName,
            'address' => $this->address,
            'action_url' => url('/admin/retailers')
        ];
    }
}
