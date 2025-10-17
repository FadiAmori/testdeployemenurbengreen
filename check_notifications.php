<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

echo "=== Checking notification_product table ===\n\n";

$rows = DB::table('notification_product')
    ->join('notifications', 'notifications.id', '=', 'notification_product.notification_id')
    ->join('products', 'products.id', '=', 'notification_product.product_id')
    ->select(
        'products.name as product_name',
        'notifications.name as notification_name',
        'notification_product.days',
        'notification_product.time'
    )
    ->limit(10)
    ->get();

foreach ($rows as $row) {
    echo "Product: {$row->product_name}\n";
    echo "Notification: {$row->notification_name}\n";
    echo "Days (raw): {$row->days}\n";
    echo "Days (type): " . gettype($row->days) . "\n";
    
    // Try to decode if it's JSON
    $decoded = json_decode($row->days, true);
    if ($decoded !== null) {
        echo "Days (decoded): " . implode(', ', $decoded) . "\n";
    }
    
    echo "Time: {$row->time}\n";
    echo "---\n";
}

echo "\n=== Current time check ===\n";
$now = Carbon::now();
echo "Current time: " . $now->format('Y-m-d H:i:s') . "\n";
echo "Current day: " . strtolower($now->format('l')) . "\n";
echo "Current time (H:i): " . $now->format('H:i') . "\n";

echo "\n=== Checking if any notifications should be sent now ===\n";

$currentTime = $now->format('H:i');
$currentDay = strtolower($now->format('l'));

$matches = DB::table('notification_product')
    ->join('notifications', 'notifications.id', '=', 'notification_product.notification_id')
    ->join('products', 'products.id', '=', 'notification_product.product_id')
    ->select(
        'products.name as product_name',
        'notifications.name as notification_name',
        'notification_product.days',
        'notification_product.time'
    )
    ->get();

$shouldSend = [];

foreach ($matches as $match) {
    $days = json_decode($match->days, true) ?: [];
    
    // Check if days is actually an integer (old format)
    if (is_numeric($match->days)) {
        echo "⚠️ WARNING: Days is numeric ({$match->days}) instead of JSON array\n";
        continue;
    }
    
    $time = null;
    if (!empty($match->time)) {
        try {
            $time = Carbon::parse($match->time)->format('H:i');
        } catch (\Exception $e) {
            $time = null;
        }
    }
    
    $dayMatch = in_array('everyday', $days) || in_array($currentDay, $days);
    $timeMatch = empty($time) || $time == $currentTime;
    
    if ($dayMatch && $timeMatch) {
        $shouldSend[] = $match;
        echo "✅ MATCH: {$match->product_name} - {$match->notification_name} (days: " . implode(', ', $days) . ", time: $time)\n";
    }
}

if (empty($shouldSend)) {
    echo "❌ No notifications match current time/day\n";
} else {
    echo "\n✅ Found " . count($shouldSend) . " notification(s) to send\n";
}

echo "\n=== Checking users who favorited products ===\n";

$usersWithFavorites = DB::table('user_product_favorites')
    ->join('users', 'users.id', '=', 'user_product_favorites.user_id')
    ->join('products', 'products.id', '=', 'user_product_favorites.product_id')
    ->select('users.id', 'users.name', 'users.email', 'user_product_favorites.product_id', 'products.name as product_name')
    ->get();

if ($usersWithFavorites->isEmpty()) {
    echo "❌ No users have favorited any products!\n";
    echo "   This is why no emails are being sent.\n";
    echo "   Users must favorite products to receive notifications.\n";
    echo "\n";
    echo "   To fix this:\n";
    echo "   1. Go to the product page (e.g., Tomate)\n";
    echo "   2. Click the favorite/heart icon\n";
    echo "   3. Make sure you're logged in as a user with a valid email\n";
} else {
    echo "✅ Found " . $usersWithFavorites->count() . " user-product favorites:\n";
    foreach ($usersWithFavorites as $fav) {
        echo "   - User: {$fav->name} ({$fav->email}) favorited: {$fav->product_name} (ID: {$fav->product_id})\n";
    }
}
