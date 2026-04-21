<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class TriggerStaffNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'staff:trigger-notifications';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Triggers visit reminders and target alerts for field staff';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting notification trigger process...');

        $fieldStaffs = \App\Models\FieldStaff::with(['user'])->get();
        $today = now();
        $startOfMonth = now()->startOfMonth();

        foreach ($fieldStaffs as $staff) {
            if (!$staff->user) continue;

            // --- 1. Target Alerts ---
            $targetAmount = \App\Models\SalesTarget::where('user_id', $staff->user->id)
                ->whereMonth('month', $today->month)
                ->whereYear('month', $today->year)
                ->sum('amount');

            if ($targetAmount > 0) {
                // Calculate Achievement (Delivered orders value this month)
                $achievement = \App\Models\RetailerOrder::where('fieldstaff_id', $staff->id)
                    ->where('status', \App\Models\RetailerOrder::STATUS_DELIVERED)
                    ->whereBetween('delivered_at', [$startOfMonth, $today])
                    ->sum('total_amount');

                $percent = ($achievement / $targetAmount) * 100;

                // Simple check: notify on reaching certain levels
                // We could use a "last_target_notification" column but for now we'll just check today's progress
                if ($percent >= 100) {
                    $staff->user->notify(new \App\Notifications\TargetAlert("Congratulations! You have reached 100% of your target.", 100));
                } elseif ($percent >= 80) {
                    $staff->user->notify(new \App\Notifications\TargetAlert("Great job! You have reached 80% of your monthly target.", 80));
                } elseif ($percent >= 50) {
                    $staff->user->notify(new \App\Notifications\TargetAlert("You have reached 50% of your monthly target.", 50));
                }
            }

            // --- 2. Visit Reminders ---
            // Find assigned retailers not visited in > 7 days
            $retailers = \App\Models\Retailer::where('field_staff_id', $staff->id)->get();
            foreach ($retailers as $retailer) {
                $lastVisit = \App\Models\VisitLog::where('customer_id', $retailer->id)
                    ->where('customer_category', 'retailer')
                    ->latest('check_in_at')
                    ->first();

                if (!$lastVisit || $lastVisit->check_in_at->diffInDays($today) >= 7) {
                    $staff->user->notify(new \App\Notifications\VisitReminder(
                        "Reminder: You haven't visited {$retailer->shop_name} in over a week.",
                        $retailer->shop_name,
                        $retailer->address
                    ));
                }
            }
        }

        $this->info('Notification process completed.');
    }
}
