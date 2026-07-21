<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class UserApprovedNotification extends Notification
{
    use Queueable;

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('承認が完了しました')
            ->line('会員専用サイトのあなたのアカウントが承認されました。')
            ->line('ログインしてサービスをご利用いただけます。')
            ->action('ログインはこちら', url('http://localhost/api/login'));
    }
}
