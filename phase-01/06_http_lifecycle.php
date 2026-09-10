<?php
// 06_http_lifecycle.php

header('Content-Type: application/json');

$requestMethod = $_SERVER['REQUEST_METHOD'];
$requestUri    = $_SERVER['REQUEST_URI'];
$userAgent     = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
$serverPort    = $_SERVER['SERVER_PORT'];

$name     = $_GET['name'] ?? null;
$category = $_GET['category'] ?? null;
$page     = (int) ($_GET['page'] ?? 1);
$perPage  = (int) ($_GET['per_page'] ?? 10);

$allProducts = [
    ["id" => 1, "name" => "Aluminium Sheet 4x8",  "price" => 2499.99, "category" => "metals",      "stock" => 50],
    ["id" => 2, "name" => "Steel Rod 10mm",        "price" => 650.00,  "category" => "metals",      "stock" => 120],
    ["id" => 3, "name" => "Copper Wire 2.5mm",     "price" => 1200.00, "category" => "electronics", "stock" => 30],
    ["id" => 4, "name" => "LED Strip Light 5m",    "price" => 450.00,  "category" => "electronics", "stock" => 80],
    ["id" => 5, "name" => "Brass Fitting Set",     "price" => 350.00,  "category" => "plumbing",    "stock" => 15],
];

$filteredProducts = $allProducts;
if ($category) {
    $filteredProducts = array_filter($allProducts, fn($p) => $p['category'] === $category);
    $filteredProducts = array_values($filteredProducts);
}

$total = count($filteredProducts);
$totalPages = max(1, (int) ceil($total / $perPage));
$offset = ($page - 1) * $perPage;
$paginatedProducts = array_slice($filteredProducts, $offset, $perPage);

$response = [
    "success" => true,
    "data"    => $paginatedProducts,
    "meta"    => [
        "current_page" => $page,
        "per_page"     => $perPage,
        "total"        => $total,
        "total_pages"  => $totalPages,
        "has_next"     => $page < $totalPages,
        "has_prev"     => $page > 1,
    ],
    "request_info" => [
        "method"       => $requestMethod,
        "uri"          => $requestUri,
        "filters"      => [
            "category"  => $category,
            "name"      => $name,
        ],
        "greeting"     => $name ? "Hello, {$name}!" : null,
    ],
    "errors"  => null,
];

if (empty($paginatedProducts) && $category) {
    http_response_code(404);
    $response["success"] = false;
    $response["errors"] = ["No products found in category: {$category}"];
} else {
    http_response_code(200);
}

echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
