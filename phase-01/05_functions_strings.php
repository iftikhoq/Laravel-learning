<?php
// 05_functions_strings.php

echo "=== BASIC FUNCTIONS ===\n\n";

function greet(string $name): string
{
    return "Welcome to our E-Store, {$name}!";
}

echo greet("Ifti") . "\n";

function formatPrice(float $amount, string $currency = "৳"): string
{
    return $currency . number_format($amount, 2);
}

echo "Price: " . formatPrice(2499.99) . "\n";
echo "Price (USD): " . formatPrice(2499.99, "$") . "\n";

echo "\n=== TYPE DECLARATIONS (PHP 8.2+) ===\n\n";

function calculateDiscount(float $price, float $percentage): float
{
    return $price - ($price * $percentage / 100);
}

echo "Original: " . formatPrice(1000) . "\n";
echo "After 15% off: " . formatPrice(calculateDiscount(1000, 15)) . "\n";

function findProduct(?int $id): ?array
{
    if ($id === null) {
        return null;
    }

    $products = [
        1 => ["name" => "Aluminium Sheet", "price" => 2499.99],
        2 => ["name" => "Steel Rod", "price" => 650.00],
    ];

    return $products[$id] ?? null;
}

$found = findProduct(1);
echo "Found: " . ($found ? $found["name"] : "Not found") . "\n";

$notFound = findProduct(99);
echo "Found: " . ($notFound ? $notFound["name"] : "Not found") . "\n";

function formatId(int|string $id): string
{
    return "PROD-" . str_pad((string) $id, 6, "0", STR_PAD_LEFT);
}

echo "ID from int: " . formatId(42) . "\n";
echo "ID from string: " . formatId("123") . "\n";

echo "\n=== ARROW FUNCTIONS & CLOSURES ===\n\n";

$addTax = fn(float $price): float => $price * 1.05;

echo "Before tax: " . formatPrice(1000) . "\n";
echo "After tax: " . formatPrice($addTax(1000)) . "\n";

$taxRate = 0.05;

$calculateTotal = function (array $items) use ($taxRate): float {
    $subtotal = 0;
    foreach ($items as $item) {
        $subtotal += $item["price"] * $item["qty"];
    }
    return $subtotal * (1 + $taxRate);
};

$cartItems = [
    ["name" => "Aluminium Sheet", "price" => 2499.99, "qty" => 2],
    ["name" => "Steel Rod",       "price" => 650.00,  "qty" => 5],
];

echo "Cart total (with 5% VAT): " . formatPrice($calculateTotal($cartItems)) . "\n";

function createProduct(
    string $name,
    float $price,
    int $stock = 0,
    bool $isFeatured = false,
    ?string $sku = null
): array {
    return compact('name', 'price', 'stock', 'isFeatured', 'sku');
}

$product = createProduct(
    name: "Copper Wire 2.5mm",
    price: 1200.00,
    isFeatured: true,
    sku: "COP-WIRE-25"
);
echo "\nNamed args product:\n";
print_r($product);

echo "\n=== STRING FUNCTIONS (the ones you'll use daily) ===\n\n";

$text = "  Hello, Modern E-Commerce Store!  ";

echo "trim: '" . trim($text) . "'\n";
echo "ltrim: '" . ltrim($text) . "'\n";
echo "rtrim: '" . rtrim($text) . "'\n";

echo "strtolower: " . strtolower("ALUMINIUM SHEET") . "\n";
echo "strtoupper: " . strtoupper("aluminium sheet") . "\n";
echo "ucfirst: " . ucfirst("aluminium sheet") . "\n";
echo "ucwords: " . ucwords("aluminium sheet 4x8") . "\n";

$description = "Premium quality aluminium sheet for industrial use";
echo "strpos: 'aluminium' found at position " . strpos($description, "aluminium") . "\n";
echo "str_contains: " . (str_contains($description, "premium") ? "No (case-sensitive)" : "") . "\n";
echo "str_contains: " . (str_contains($description, "Premium") ? "Yes" : "No") . "\n";
echo "str_starts_with: " . (str_starts_with($description, "Premium") ? "Yes" : "No") . "\n";
echo "str_ends_with: " . (str_ends_with($description, "use") ? "Yes" : "No") . "\n";

echo "str_replace: " . str_replace("aluminium", "aluminum", $description) . "\n";

$csvCategories = "Electronics,Furniture,Clothing,Auto Parts";
$categoryArray = explode(",", $csvCategories);
echo "explode: ";
print_r($categoryArray);

$joined = implode(" | ", $categoryArray);
echo "implode: " . $joined . "\n";

echo "substr: " . substr($description, 0, 15) . "...\n";
echo "strlen: " . strlen($description) . " characters\n";

function generateSlug(string $text): string
{
    $slug = strtolower($text);
    $slug = preg_replace('/[^a-z0-9\s-]/', '', $slug);
    $slug = preg_replace('/[\s-]+/', '-', $slug);
    return trim($slug, '-');
}

echo "Slug: " . generateSlug("Aluminium Sheet 4x8 (Premium)") . "\n";

echo "\n--- Script 05 Complete ---\n";
