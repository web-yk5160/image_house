<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Auth\Notifications\ResetPassword as Notification;
use Illuminate\Notifications\Messages\MailMessage;


class ResetPassword extends Notification
{

    public function toMail($notifiable)
    {
        $url = url(config('app.client_url').'/password/reset/'.$this->token).
                    '?email='.urlencode($notifiable->email);
        return (new MailMessage)
                    ->line('アカウントのパスワード再設定リクエストを受け取ったので、このメールを受信して​​います')
                    ->action('Reset Password', $url)
                    ->line('パスワードのリセットを要求しなかった場合、これ以上のアクションは必要ありません。');
    }

}
