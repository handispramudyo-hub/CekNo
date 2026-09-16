<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ResetPasswordNotification extends Notification
{
    use Queueable;

    public function __construct(
        public string $token,
        public string $email,
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $url = rtrim(config('app.url'), '/')
            .'/reset-password?token='.$this->token
            .'&email='.urlencode($this->email);

        return (new MailMessage)
            ->subject('Reset Kata Sandi CekNO')
            ->greeting('Halo '.$notifiable->name.'!')
            ->line('Kami menerima permintaan reset kata sandi akun CekNO Anda.')
            ->action('Reset Kata Sandi', $url)
            ->line('Tautan ini berlaku 60 menit. Jika bukan Anda yang meminta, abaikan email ini.');
    }
}