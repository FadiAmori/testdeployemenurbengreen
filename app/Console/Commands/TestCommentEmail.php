<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use App\Models\Statute;
use App\Models\Comente;
use App\Models\User;
use App\Mail\CommentNotificationMail;

class TestCommentEmail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:comment-email {email}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test the comment notification email system';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');
        
        $this->info("Testing comment notification email to: {$email}");
        
        // Create mock statute object (not persisted to database)
        $statute = new Statute();
        $statute->id = 999;
        $statute->titre = 'Test Statute - Environmental Protection';
        $statute->description = 'This is a test statute about environmental protection in urban areas.';
        $statute->exists = true; // Mark as existing to prevent save operations
        
        // Create mock comment object (not persisted to database)
        $comment = new Comente();
        $comment->id = 999;
        $comment->description = 'This is a test comment. Great statute! I totally agree with this approach.';
        $comment->statute_id = 999;
        $comment->exists = true; // Mark as existing to prevent save operations
        
        // Create mock commenter object (not persisted to database)
        $commenter = new User();
        $commenter->id = 999;
        $commenter->name = 'Test Commenter';
        $commenter->email = 'test.commenter@example.com';
        $commenter->exists = true; // Mark as existing to prevent save operations
        
        try {
            Mail::to($email)->send(new CommentNotificationMail($statute, $comment, $commenter));
            
            $this->info("✅ Email sent successfully to {$email}!");
            $this->info("📧 Check your inbox (or spam folder).");
            $this->info("");
            $this->info("Email Details:");
            $this->info("- Statute: {$statute->titre}");
            $this->info("- Comment: {$comment->description}");
            $this->info("- Commenter: {$commenter->name}");
            
            if (config('mail.default') === 'log') {
                $this->info("");
                $this->warn("⚠️  You're using the 'log' mail driver.");
                $this->info("Check the email in: storage/logs/laravel.log");
            }
        } catch (\Exception $e) {
            $this->error("❌ Failed to send email: " . $e->getMessage());
            $this->error("");
            $this->info("Troubleshooting:");
            $this->info("1. Check your .env mail configuration");
            $this->info("2. Run: php artisan config:clear");
            $this->info("3. For testing, try setting MAIL_MAILER=log in .env");
        }
    }
}
