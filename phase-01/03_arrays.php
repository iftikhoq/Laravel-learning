<?php
// 03_arrays.php

echo "=== INDEXED ARRAYS (like Python lists) ===\n\n";

$categories = ["Electronics", "Furniture", "Clothing", "Auto Parts"];

echo "Categories: \n";
print_r($categories);

echo "First: " . $categories[0] . "\n";
echo "Last: " . end($categories) . "\n";

$categories[] = "Kitchen";
array_push($categories, "Tools");

echo "Total categories: " . count($categories) . "\n";

echo "Has 'Clothing': " . (in_array("Clothing", $categories) ? "Yes" : "No") . "\n";

$firstThree = array_slice($categories, 0, 3);
echo "First 3: " . implode(", ", $firstThree) . "\n";

echo "\n=== ASSOCIATIVE ARRAYS (like Python dicts) ===\n\n";

$product = [
    "id" => 1,
    "name" => "Aluminium Rod 12mm",
    "price" => 850.00,
    "stock" => 75,
    "category" => "Auto Parts",
    "is_featured" => true,
];

echo "Product: " . $product["name"] . "\n";
echo "Price: ৳" . number_format($product["price"], 2) . "\n";

$product["sku"] = "ALU-ROD-12MM";
$product["price"] = 899.00;

echo "Has 'sku': " . (array_key_exists("sku", $product) ? "Yes" : "No") . "\n";
echo "Has 'discount': " . (isset($product["discount"]) ? "Yes" : "No") . "\n";

echo "Keys: " . implode(", ", array_keys($product)) . "\n";

echo "\nAll product fields:\n";
foreach ($product as $key => $value) {
    $displayValue = is_bool($value) ? ($value ? 'true' : 'false') : $value;
    echo "  {$key}: {$displayValue}\n";
}

echo "\n=== MULTI-DIMENSIONAL ARRAYS (nested structures) ===\n\n";

$products = [
    [
        "id" => 1,
        "name" => "Aluminium Sheet 4x8",
        "price" => 2499.99,
        "stock" => 50,
    ],
    [
        "id" => 2,
        "name" => "Steel Rod 10mm",
        "price" => 650.00,
        "stock" => 120,
    ],
    [
        "id" => 3,
        "name" => "Copper Wire 2.5mm",
        "price" => 1200.00,
        "stock" => 30,
    ],
];

echo "Product catalog:\n";
foreach ($products as $index => $prod) {
    echo "  [{$index}] {$prod['name']} — ৳" . number_format($prod['price'], 2) . " ({$prod['stock']} in stock)\n";
}

echo "\n=== USEFUL ARRAY FUNCTIONS ===\n\n";

$prices = [2499.99, 650.00, 1200.00, 899.00, 350.00];

echo "Min price: ৳" . min($prices) . "\n";
echo "Max price: ৳" . max($prices) . "\n";
echo "Sum: ৳" . array_sum($prices) . "\n";
echo "Average: ৳" . number_format(array_sum($prices) / count($prices), 2) . "\n";

sort($prices);
echo "Sorted ASC: " . implode(", ", $prices) . "\n";

rsort($prices);
echo "Sorted DESC: " . implode(", ", $prices) . "\n";

$discountedPrices = array_map(function ($price) {
    return $price * 0.9;
}, $prices);
echo "10% off: " . implode(", ", array_map(fn($p) => number_format($p, 2), $discountedPrices)) . "\n";

$expensiveProducts = array_filter($prices, fn($price) => $price > 1000);
echo "Above ৳1000: " . implode(", ", $expensiveProducts) . "\n";

$morePrices = [175.00, 425.00];
$allPrices = [...$prices, ...$morePrices];
echo "Combined: " . count($allPrices) . " prices\n";

echo "\n--- Script 03 Complete ---\n";
