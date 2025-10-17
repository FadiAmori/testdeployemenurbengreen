<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class ProductNotificationMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $user;
    public $product;
    public $notification;

    public function __construct($user, $product, $notification)
    {
        $this->user = $user;
        $this->product = $product;
        $this->notification = $notification;
    }

    public function build()
    {
        return $this->subject("Notification produit: {$this->product->name}")
                    ->view('emails.product_notification')
                    ->with([
                        'user' => $this->user,
                        'product' => $this->product,
                        'notification' => $this->notification,
                    ]);
    }
}
