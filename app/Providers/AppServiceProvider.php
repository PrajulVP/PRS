<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use App\View\Composers\PendingUsersComposer;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Facades\Lang;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Schema::defaultStringLength(191);
        View::composer('layouts.partials.sidebar', PendingUsersComposer::class);

        // Register Observers
        \App\Models\RetailerOrder::observe(\App\Observers\RetailerOrderObserver::class);
        \App\Models\DistributorOrder::observe(\App\Observers\DistributorOrderObserver::class);
        \App\Models\FieldStaff::observe(\App\Observers\FieldStaffObserver::class);

        // Customize Password Reset Email
        ResetPassword::toMailUsing(function ($notifiable, $token) {
            $url = url(route('password.reset', [
                'token' => $token,
                'email' => $notifiable->getEmailForPasswordReset(),
            ], false));

            $customSalutation = new HtmlString('<div style="margin-top: 40px; border-top: 1px solid #edf2f7; padding-top: 20px; font-size: 14px; text-align: left;">
                    Best Regards,<br>
                    <strong>Security Team</strong><br>
                    Atomed Wellness Private Limited
                </div>');

            return (new MailMessage)
                ->subject(Lang::get('Reset Password Notification'))
                ->line(Lang::get('You are receiving this email because we received a password reset request for your account.'))
                ->action(Lang::get('Reset Password'), $url)
                ->line(Lang::get('This password reset link will expire in :count minutes.', ['count' => config('auth.passwords.'.config('auth.defaults.passwords').'.expire')]))
                ->line(Lang::get('If you did not request a password reset, no further action is required.'))
                ->salutation($customSalutation);
        });
    }
}
