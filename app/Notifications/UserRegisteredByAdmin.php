<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class UserRegisteredByAdmin extends Notification
{
    use Queueable;

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('会員専用サイトのアカウントが作成されました')
            ->line('会員専用サイトのあなたのアカウントが作成されました。')
            ->line('ログインしてご利用を開始できます。')

            ->action('ログインはこちら', url('http://localhost/api/login'))

            ->line('ログイン時のメールアドレスはこのメールの届いているメールアドレスです。')
            ->line('パスワードも同様ですが、セキュリティのため、パスワードの変更をお願いいたします。');
    }
}
