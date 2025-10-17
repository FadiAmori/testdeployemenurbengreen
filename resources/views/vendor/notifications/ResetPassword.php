<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Lang;

class ResetPassword extends Notification
{
    use Queueable;

    public $token;

    public function __construct($token)
    {
        $this->token = $token;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $url = url(route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ], false));

        $mail = (new MailMessage)
            ->subject(Lang::get('Reset Your UrbanGreen Password'))
            ->line(Lang::get('You are receiving this email because we received a password reset request for your UrbanGreen account.'))
            ->action(Lang::get('Reset Password'), $url)
            ->line(Lang::get('This password reset link will expire in :count minutes.', ['count' => config('auth.passwords.users.expire')]))
            ->line(Lang::get('If you did not request a password reset, no further action is required.'))
            ->line('Thank you for using UrbanGreen!');

        // Log the rendered HTML to debug DOMDocument issue
        $html = $mail->render();
        \Log::info('ResetPassword HTML: ' . $html);
        if (empty($html)) {
            \Log::error('Empty HTML in ResetPassword notification');
            // Fallback to plain text if HTML is empty
            return (new MailMessage)
                ->subject(Lang::get('Reset Your UrbanGreen Password'))
                ->line(Lang::get('You are receiving this email because we received a password reset request for your UrbanGreen account.'))
                ->line('Click here to reset your password: ' . $url)
                ->line(Lang::get('This password reset link will expire in :count minutes.', ['count' => config('auth.passwords.users.expire')]))
                ->line(Lang::get('If you did not request a password reset, no further action is required.'))
                ->line('Thank you for using UrbanGreen!');
        }

        // If DOMDocument is used (hypothetical fix)
        try {
            $dom = new \DOMDocument();
            @$dom->loadHTML($html); // Suppress warnings
            // Add DOMDocument logic here if needed
            \Log::info('DOMDocument processed successfully');
        } catch (\Exception $e) {
            \Log::error('DOMDocument error in ResetPassword: ' . $e->getMessage());
        }

        return $mail;
    }
}