# Comment Email Notification System

## Overview
This system sends email notifications to statute authors when someone comments on their statutes.

## Features
- ✅ Tracks who created each statute (`user_id` in statutes table)
- ✅ Tracks who made each comment (`user_id` in comentes table)
- ✅ Sends beautiful HTML email notifications
- ✅ Only sends email if:
  - The statute has an author with an email address
  - The commenter is different from the statute author (no self-notifications)

## Files Modified/Created

### 1. Migration
**File**: `database/migrations/2025_10_14_000000_add_user_id_to_statutes_and_comentes.php`
- Adds `user_id` column to `statutes` table
- Adds `user_id` column to `comentes` table
- Creates foreign key relationships with `users` table

### 2. Models Updated
**Files**:
- `app/Models/Statute.php` - Added `user()` relationship and `user_id` to fillable
- `app/Models/Comente.php` - Added `user()` relationship and `user_id` to fillable

### 3. Mailable Class
**File**: `app/Mail/CommentNotificationMail.php`
- Handles email construction
- Queued for async sending (implements `ShouldQueue`)

### 4. Email Template
**File**: `resources/views/emails/comment_notification.blade.php`
- Beautiful HTML email with green theme
- Shows statute title, commenter name, and comment text
- Includes "View Your Statute" button

### 5. Controllers Updated
**Files**:
- `app/Http/Controllers/StatuteController.php` - Saves `user_id` when creating statute
- `app/Http/Controllers/ComenteController.php` - Saves `user_id` and sends email notification

## Email Configuration

### For Development (Using Mailtrap)
Add to your `.env` file:
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_mailtrap_username
MAIL_PASSWORD=your_mailtrap_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@urbangreen.com
MAIL_FROM_NAME="${APP_NAME}"
```

### For Production (Using Gmail)
Add to your `.env` file:
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your_email@gmail.com
MAIL_PASSWORD=your_app_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your_email@gmail.com
MAIL_FROM_NAME="${APP_NAME}"
```

**Note**: For Gmail, you need to create an [App Password](https://support.google.com/accounts/answer/185833).

### For Testing (Log emails to file)
Add to your `.env` file:
```env
MAIL_MAILER=log
```
Emails will be written to `storage/logs/laravel.log`

## Queue Configuration (Optional but Recommended)

For async email sending, configure queue driver in `.env`:
```env
QUEUE_CONNECTION=database
```

Then run:
```bash
php artisan queue:table
php artisan migrate
php artisan queue:work
```

## How It Works

1. **User creates a statute** → `user_id` is saved with the statute
2. **Another user comments on the statute** → Comment is created with commenter's `user_id`
3. **Email is sent** → Beautiful HTML email notification is sent to the statute author
4. **Error handling** → If email fails, error is logged but comment is still saved

## Testing

1. Start your Laravel server:
```bash
php artisan serve
```

2. Make sure you're logged in (authentication required)

3. Create a statute (your user_id will be saved)

4. Log in as a different user and comment on the statute

5. Check your email inbox (or logs if using `log` driver)

## Troubleshooting

**Q: Emails not sending?**
- Check `.env` mail configuration
- Run `php artisan config:clear`
- Check `storage/logs/laravel.log` for errors

**Q: Queue not processing?**
- Run `php artisan queue:work` in a separate terminal
- Or use `MAIL_MAILER=log` for immediate sync sending

**Q: Getting "user_id cannot be null" errors?**
- Make sure users are authenticated before creating statutes/comments
- Run `php artisan migrate` to ensure columns exist

## Future Enhancements

- [ ] Add notification preferences (allow users to opt-out)
- [ ] Group multiple comments into digest emails
- [ ] Add in-app notification badges
- [ ] Email notification for reactions (likes/dislikes)
