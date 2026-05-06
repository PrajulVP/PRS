<?php

namespace App\Notifications;

use App\Models\ReturnRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ReturnRequestNotification extends Notification
{
    use Queueable;

    protected $returnRequest;
    protected $type; // created, approved, rejected, completed
    protected $actor;

    /**
     * Create a new notification instance.
     */
    public function __construct(ReturnRequest $returnRequest, $type, $actor)
    {
        $this->returnRequest = $returnRequest;
        $this->type = $type;
        $this->actor = $actor;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database']; // Defaulting to database notifications
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $message = "";
        $url = route('admin.returns.index');

        switch ($this->type) {
            case 'created':
                $message = "New return request {$this->returnRequest->return_code} created by {$this->actor->name}.";
                break;
            case 'approved':
                $message = "Return request {$this->returnRequest->return_code} approved by {$this->actor->name} (Tier 1).";
                break;
            case 'rejected':
                $message = "Return request {$this->returnRequest->return_code} rejected by {$this->actor->name}. Reason: {$this->returnRequest->rejection_reason}";
                break;
            case 'completed':
                $message = "Return request {$this->returnRequest->return_code} completed. Credit note generated.";
                break;
        }

        return [
            'return_request_id' => $this->returnRequest->id,
            'order_code' => $this->returnRequest->return_code, // Mapped to order_code for system-wide highlight compatibility
            'type' => $this->type,
            'message' => $message,
            'action_url' => $url, // Base URL for the returns module
            'actor_id' => $this->actor->id,
            'actor_name' => $this->actor->name,
        ];
    }
}
