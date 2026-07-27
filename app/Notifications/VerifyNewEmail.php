<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\URL;

class VerifyNewEmail extends Notification
{
    use Queueable;

    public $newEmail;

    /**
     * Create a new notification instance.
     */
    public function __construct($newEmail)
    {
        $this->newEmail = $newEmail;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        // 新しいメールアドレスをハッシュ化してURLに含める
        $url = URL::temporarySignedRoute(
            'verification.verify.new', // メール変更専用のルート名
            now()->addMinutes(60),
            [
                'id' => $notifiable->getKey(),
                'hash' => sha1($this->newEmail),
                'email' => urlencode($this->newEmail),
            ]
        );

        return (new MailMessage)
            ->subject('【重要】メールアドレス変更の確認')
            ->line('メールアドレスの変更リクエストを受け付けました。')
            ->action('メールアドレスを変更する', $url)
            ->line('このリンクの有効期限は60分です。心当たりがない場合はこのメールを無視してください。');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
