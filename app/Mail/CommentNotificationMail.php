<?php

namespace App\Mail;

use App\Models\Comente;
use App\Models\Statute;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class CommentNotificationMail extends Mailable implements ShouldQueue
{
    use Queueable;

    public $statute;
    public $comment;
    public $commenter;

    /**
     * Create a new message instance.
     */
    public function __construct(Statute $statute, Comente $comment, User $commenter)
    {
        // Store as simple properties instead of serializing models
        $this->statute = $statute;
        $this->comment = $comment;
        $this->commenter = $commenter;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject("New Comment on Your Statute: {$this->statute->titre}")
                    ->view('emails.comment_notification')
                    ->with([
                        'statute' => $this->statute,
                        'comment' => $this->comment,
                        'commenter' => $this->commenter,
                    ]);
    }
}
