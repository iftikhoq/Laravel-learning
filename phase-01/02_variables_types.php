<?php
// 02_variables_types.php

echo "=== VARIABLES & DATA TYPES ===\n\n";

$productName = "Aluminium Sheet 4x8";
echo "Product: " . $productName . "\n";

$stockQuantity = 150;
echo "Stock: " . $stockQuantity . "\n";

$price = 2499.99;
echo "Price: ৳" . $price . "\n";

$isAvailable = true;
echo "Available: " . ($isAvailable ? "Yes" : "No") . "\n";

$discountCode = null;
echo "Discount: " . ($discountCode ?? "None") . "\n";

echo "\n=== TYPE CHECKING ===\n\n";

echo "Type of \$productName: " . gettype($productName) . "\n";
echo "Type of \$stockQuantity: " . gettype($stockQuantity) . "\n";
echo "Type of \$price: " . gettype($price) . "\n";
echo "Type of \$isAvailable: " . gettype($isAvailable) . "\n";
echo "Type of \$discountCode: " . gettype($discountCode) . "\n";

echo "\nvar_dump examples:\n";
var_dump($productName);
var_dump($stockQuantity);
var_dump($price);
var_dump($isAvailable);
var_dump($discountCode);

echo "\n=== TYPE CASTING ===\n\n";

$stringNumber = "42";
$intVersion = (int) $stringNumber;
$floatVersion = (float) $stringNumber;
echo "String '42' → int: " . $intVersion . " (type: " . gettype($intVersion) . ")\n";
echo "String '42' → float: " . $floatVersion . " (type: " . gettype($floatVersion) . ")\n";

echo "\nType Juggling (automatic conversions):\n";
echo "  '5' + 3 = " . ('5' + 3) . "\n";
echo "  true + true = " . (true + true) . "\n";
echo "  '10 apples' + 5 = " . ('10 apples' + 5) . "\n";

echo "\n=== CONSTANTS ===\n\n";

define('TAX_RATE', 0.05);

const SHOP_NAME = "Modern E-Store";
const CURRENCY = "BDT";

echo "Shop: " . SHOP_NAME . "\n";
echo "Tax Rate: " . (TAX_RATE * 100) . "%\n";
echo "Currency: " . CURRENCY . "\n";

echo "\n--- Script 02 Complete ---\n";
