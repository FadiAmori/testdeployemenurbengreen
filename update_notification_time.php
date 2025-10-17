<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

// Get current time
$now = Carbon::now();
$targetTime = $now->copy()->addMinutes(1)->format('H:i:00'); // 1 minute from now
$currentDay = strtolower($now->format('l')); // Get current day (monday, tuesday, etc.)

echo "Current time: " . $now->format('H:i:s') . "\n";
echo "Current day: $currentDay\n";
echo "Setting notification time to: $targetTime\n\n";

// Update the Fertilisation notification for Tomate (product_id = 1) to trigger in 1 minute
$updated = DB::table('notification_product')
    ->join('notifications', 'notifications.id', '=', 'notification_product.notification_id')
    ->where('notification_product.product_id', 1)
    ->where('notifications.name', 'Fertilisation')
    ->update([
        'notification_product.time' => $targetTime,
        'notification_product.days' => json_encode([$currentDay]) // Use current day
    ]);

echo "✅ Updated $updated row(s)\n\n";

// Verify
$check = DB::table('notification_product')
    ->join('notifications', 'notifications.id', '=', 'notification_product.notification_id')
    ->join('products', 'products.id', '=', 'notification_product.product_id')
    ->where('notification_product.product_id', 1)
    ->where('notifications.name', 'Fertilisation')
    ->select(
        'products.name as product',
        'notifications.name as notification',
        'notification_product.days',
        'notification_product.time'
    )
    ->first();

if ($check) {
    echo "Verification:\n";
    echo "  Product: {$check->product}\n";
    echo "  Notification: {$check->notification}\n";
    echo "  Days: {$check->days}\n";
    echo "  Time: {$check->time}\n";
    echo "\nThe notification will be sent at $targetTime (in ~1 minute)\n";
    echo "Check your email: fadiamorri2002@gmail.com and Tesnim.FekihHasinSnene@esprit.tn\n";
}
