<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword as ResetPasswordBase;
use Illuminate\Notifications\Messages\MailMessage;

class ResetPasswordNotification extends ResetPasswordBase
{
    /**
     * @param  mixed  $notifiable
     */
    public function toMail($notifiable): MailMessage
    {
        $url = $this->resetUrl($notifiable);
        $expire = (int) config('auth.passwords.'.config('auth.defaults.passwords').'.expire', 60);
        $name = $notifiable->name ?? 'olá';

        return (new MailMessage)
            ->subject('Recuperar senha — '.config('app.name', 'MarkCraft'))
            ->view('emails.reset-password', [
                'url' => $url,
                'expire' => $expire,
                'userName' => $name,
                'appName' => config('app.name', 'MarkCraft'),
                'appUrl' => rtrim((string) config('app.url'), '/'),
                'logoUrl' => asset('brand/icon-192.png'),
                'markUrl' => asset('brand/markcraft-mark.svg'),
            ]);
    }
}
