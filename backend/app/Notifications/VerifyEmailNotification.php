<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class VerifyEmailNotification extends Notification
{
    use Queueable;

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $url = rtrim(config('app.url'), '/')
            .'/verify-email?id='.$notifiable->getKey()
            .'&hash='.sha1($notifiable->getEmailForVerification());

        return (new MailMessage)
            ->subject('Verifikasi Email CekNO')
            ->greeting('Halo '.$notifiable->name.'!')
            ->line('Terima kasih telah mendaftar di CekNO.')
            ->action('Verifikasi Email', $url)
            ->line('Jika Anda tidak membuat akun ini, abaikan email ini.');
    }
}