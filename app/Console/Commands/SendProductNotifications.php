<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Shop\Product;
use App\Mail\ProductNotificationMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class SendProductNotifications extends Command
{
    protected $signature = 'notifications:send';

    protected $description = 'Send scheduled product notifications to users who favorited products';

    public function handle()
    {
    $now = Carbon::now();
    $currentTime = $now->format('H:i'); // hours:minutes
    $currentDay = strtolower($now->format('l')); // monday, tuesday, ...

        // get products that have notifications with pivot days/time matching now
        $products = Product::whereHas('notifications', function ($q) use ($currentDay, $currentTime) {
            $q->where(function($q2) use ($currentDay, $currentTime) {
                $q2->whereRaw("JSON_CONTAINS(notification_product.days, '\"everyday\"')")
                   ->orWhereRaw("JSON_CONTAINS(notification_product.days, '\"{$currentDay}\"')");
            });

            // match time if set (or null -> any time)
            // Use TIME_FORMAT to compare hours:minutes to avoid mismatch with seconds stored in DB
            $q->where(function($q3) use ($currentTime) {
                $q3->whereNull('notification_product.time')
                   ->orWhereRaw("TIME_FORMAT(notification_product.time, '%H:%i') = ?", [$currentTime]);
            });
        })->with(['notifications', 'favoritedByUsers'])->get();

        foreach ($products as $product) {
            foreach ($product->notifications as $notification) {

                // check pivot days/time again for safety
                $days = json_decode($notification->pivot->days ?? '[]', true) ?: [];
                $time = $notification->pivot->time;

                // Normalize stored time to H:i for comparison (DB time may have seconds)
                $storedTime = null;
                if (! empty($time)) {
                    try {
                        $storedTime = Carbon::parse($time)->format('H:i');
                    } catch (\Exception $e) {
                        $storedTime = null;
                    }
                }

                $dayMatch = in_array('everyday', $days) || in_array($currentDay, $days);
                $timeMatch = empty($storedTime) || $storedTime == $currentTime;

                if (! $dayMatch || ! $timeMatch) {
                    continue;
                }

                foreach ($product->favoritedByUsers as $user) {
                    try {
                        // Queue the mail (Mailable implements ShouldQueue)
                        Mail::to($user->email)->queue(new ProductNotificationMail($user, $product, $notification));
                        Log::info("Queued product notification: product={$product->id} notification={$notification->id} user={$user->id}");
                    } catch (\Exception $e) {
                        Log::error('Failed to queue product notification email: ' . $e->getMessage(), [
                            'product_id' => $product->id,
                            'notification_id' => $notification->id,
                            'user_id' => $user->id,
                        ]);
                    }
                }
            }
        }

        $this->info('Notifications processed at ' . $now->toDateTimeString());

        return 0;
    }
}
