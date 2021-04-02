<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\URL;

class QuizResult extends Notification
{
    use Queueable;

    public $base64;
    public $text;
    public $textbook;

    public function __construct($base64, $text, $textbook)
    {
        $this->base64 = $base64;
        $this->text = $text;
        $this->textbook = $textbook;
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
        $dashboardLink = url(config('app.url').'/home');
        $libraryLink = url(config('app.url').'/library');

        return ( new MailMessage )
            ->subject('ReadO(1) Quiz Result')
            ->line('You have completed a quiz for Text: '.$this->text.', Textbook: '.$this->textbook)
            ->line('Your result is attached to this email as a PDF.')
            ->line('You can access the quiz result by going to your dashboard.')
            ->line('Access other textbooks and text by going to the library.')
            ->line('Thank you for using ReadO(1)')
            ->attachData(base64_decode($this->base64), 'quiz_result.pdf', ['mime'=>'pdf'])
            ->markdown( 'vendor.notifications.quiz_results', [
                    'greeting' => 'Hello! '.$notifiable->name,
                    'actionText1' => 'Go to dashboard', 'actionText2' => 'Library',
                    'dashboardLink' => $dashboardLink, 'libraryLink' => $libraryLink
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
