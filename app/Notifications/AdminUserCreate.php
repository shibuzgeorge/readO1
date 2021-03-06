<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\URL;

class AdminUserCreate extends Notification
{
    use Queueable;

    public $token;

    public function __construct($token)
    {
        $this->token = $token;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $url = URL::temporarySignedRoute(
            'verification.verify', Carbon::now()->addMinutes(60), ['user' => $notifiable->id]
        );
        $verificationEmailLink = str_replace('/api', '', $url);

        $resetPasswordLink = url(config('app.url').'/password/reset/'.$this->token).'?email='.urlencode($notifiable->email);
        return ( new MailMessage )
            ->subject('ReadO(1) New User Verify and Reset Password')
            ->line('You are receiving this email because an Admin has created you as a new user.')
            ->line('Please verify your email address now and then click reset password in order for you to login to your account.')
            ->line('If you do not want to sign up then no further action is required.')
            ->markdown( 'vendor.notifications.new_user', [
                'greeting' => 'Hello! '.$notifiable->name,
                'actionText1' => 'Verify Email', 'actionText2' => 'Reset Password',
                    'verificationEmailLink' => $verificationEmailLink, 'resetPasswordLink' => $resetPasswordLink
                ]

            );
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
