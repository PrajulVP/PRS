<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LoyaltyRewardApproved extends Notification
{
    use Queueable;

    protected $redemptionId;
    protected $retailerName;
    protected $rewardName;

    /**
     * Create a new notification instance.
     */
    public function __construct($redemptionId, $retailerName, $rewardName)
    {
        $this->redemptionId = $redemptionId;
        $this->retailerName = $retailerName;
        $this->rewardName = $rewardName;
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
            'redemption_id' => $this->redemptionId,
            'message' => "Reward '{$this->rewardName}' approved for retailer '{$this->retailerName}'. Please distribute the gift.",
            'type' => 'loyalty_redemption',
        ];
    }
}
