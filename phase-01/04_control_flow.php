<?php
// 04_control_flow.php

echo "=== IF / ELSEIF / ELSE ===\n\n";

$stock = 5;
$productName = "Aluminium Sheet 4x8";

if ($stock <= 0) {
    $status = "Out of Stock";
    $badge = "❌";
} elseif ($stock <= 10) {
    $status = "Low Stock";
    $badge = "⚠️";
} else {
    $status = "In Stock";
    $badge = "✅";
}

echo "{$badge} {$productName}: {$status} ({$stock} remaining)\n";

$label = $stock > 0 ? "Available" : "Unavailable";
echo "Quick check: {$label}\n";

$discount = null;
$appliedDiscount = $discount ?? 0;
echo "Discount: {$appliedDiscount}%\n";

$discount ??= 10;
echo "After ??= : Discount is now {$discount}%\n";

echo "\n=== SWITCH vs MATCH ===\n\n";

$orderStatus = "processing";

echo "Switch result: ";
switch ($orderStatus) {
    case "pending":
        echo "⏳ Order is pending confirmation\n";
        break;
    case "processing":
        echo "🔄 Order is being processed\n";
        break;
    case "shipped":
        echo "📦 Order has been shipped\n";
        break;
    case "delivered":
        echo "✅ Order delivered\n";
        break;
    default:
        echo "❓ Unknown status\n";
}

$statusMessage = match ($orderStatus) {
    "pending"    => "⏳ Order is pending confirmation",
    "processing" => "🔄 Order is being processed",
    "shipped"    => "📦 Order has been shipped",
    "delivered"  => "✅ Order delivered",
    "cancelled"  => "🚫 Order cancelled",
    default      => "❓ Unknown status",
};
echo "Match result: {$statusMessage}\n";

$httpCode = 404;
$httpMessage = match (true) {
    $httpCode >= 200 && $httpCode < 300 => "Success",
    $httpCode >= 300 && $httpCode < 400 => "Redirect",
    $httpCode >= 400 && $httpCode < 500 => "Client Error",
    $httpCode >= 500                     => "Server Error",
    default                              => "Unknown",
};
echo "HTTP {$httpCode}: {$httpMessage}\n";

echo "\n=== LOOPS ===\n\n";

echo "for loop — First 5 product IDs:\n";
for ($i = 1; $i <= 5; $i++) {
    echo "  Product #{$i}\n";
}

echo "\nwhile loop — Countdown:\n";
$countdown = 3;
while ($countdown > 0) {
    echo "  {$countdown}...\n";
    $countdown--;
}
echo "  Launch!\n";

$products = [
    ["name" => "Aluminium Sheet", "price" => 2499.99, "stock" => 50],
    ["name" => "Steel Rod",       "price" => 650.00,  "stock" => 0],
    ["name" => "Copper Wire",     "price" => 1200.00, "stock" => 30],
    ["name" => "Brass Fitting",   "price" => 350.00,  "stock" => 3],
];

echo "\nforeach — Product inventory report:\n";
echo str_pad("Name", 20) . str_pad("Price", 12) . str_pad("Stock", 10) . "Status\n";
echo str_repeat("-", 55) . "\n";

foreach ($products as $product) {
    $name = str_pad($product["name"], 20);
    $price = str_pad("৳" . number_format($product["price"], 2), 12);
    $stock = str_pad($product["stock"], 10);

    if ($product["stock"] <= 0) {
        $status = "❌ OUT";
    } elseif ($product["stock"] <= 5) {
        $status = "⚠️ LOW";
    } else {
        $status = "✅ OK";
    }

    echo "{$name}{$price}{$stock}{$status}\n";
}

echo "\nforeach with index:\n";
foreach ($products as $index => $product) {
    echo "  [{$index}] {$product['name']}\n";
}

echo "\nbreak/continue demo — find first out-of-stock:\n";
foreach ($products as $product) {
    if ($product["stock"] > 0) {
        continue;
    }
    echo "  Found: {$product['name']} is out of stock!\n";
    break;
}

echo "\n--- Script 04 Complete ---\n";
