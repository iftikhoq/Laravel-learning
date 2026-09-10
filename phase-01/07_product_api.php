<?php
// 07_product_api.php

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$products = [
    ["id" => 1,  "name" => "Aluminium Sheet 4x8",     "price" => 2499.99, "category" => "metals",      "stock" => 50,  "featured" => true],
    ["id" => 2,  "name" => "Steel Rod 10mm",           "price" => 650.00,  "category" => "metals",      "stock" => 120, "featured" => false],
    ["id" => 3,  "name" => "Copper Wire 2.5mm",        "price" => 1200.00, "category" => "electronics", "stock" => 30,  "featured" => true],
    ["id" => 4,  "name" => "PVC Pipe 4 inch",          "price" => 450.00,  "category" => "plumbing",    "stock" => 80,  "featured" => false],
    ["id" => 5,  "name" => "Cement Bag 50kg",          "price" => 520.00,  "category" => "building_materials", "stock" => 200, "featured" => true],
    ["id" => 6,  "name" => "Safety Helmet",            "price" => 250.00,  "category" => "safety",        "stock" => 150, "featured" => false],
    ["id" => 7,  "name" => "LED Bulb 12W",             "price" => 120.00,  "category" => "electronics", "stock" => 300, "featured" => false],
    ["id" => 8,  "name" => "Iron Nail 2 inch",           "price" => 80.00,   "category" => "metals",      "stock" => 500, "featured" => false],
    ["id" => 9,  "name" => "Wooden Door",              "price" => 5500.00, "category" => "furniture",   "stock" => 15,  "featured" => true],
    ["id" => 10, "name" => "Paint Brush Set",          "price" => 350.00,  "category" => "paints",        "stock" => 60,  "featured" => false],
    ["id" => 11, "name" => "Sanitary WCB",             "price" => 3200.00, "category" => "plumbing",    "stock" => 25,  "featured" => true],
    ["id" => 12, "name" => "Electrical Switchboard",     "price" => 450.00,  "category" => "electronics", "stock" => 180, "featured" => false],
];

$category  = $_GET['category'] ?? null;
$search    = $_GET['search'] ?? null;
$minPrice  = isset($_GET['min_price']) ? (float) $_GET['min_price'] : null;
$maxPrice  = isset($_GET['max_price']) ? (float) $_GET['max_price'] : null;
$sort      = $_GET['sort'] ?? null;
$featured  = isset($_GET['featured']) ? filter_var($_GET['featured'], FILTER_VALIDATE_BOOLEAN) : null;
$page      = max(1, (int) ($_GET['page'] ?? 1));
$perPage   = min(50, max(1, (int) ($_GET['per_page'] ?? 10)));

$results = $products;

if ($category !== null) {
    $results = array_filter($results, fn($p) => strtolower($p['category']) === strtolower($category));
}

if ($search !== null) {
    $results = array_filter($results, fn($p) => str_contains(strtolower($p['name']), strtolower($search)));
}

if ($minPrice !== null) {
    $results = array_filter($results, fn($p) => $p['price'] >= $minPrice);
}
if ($maxPrice !== null) {
    $results = array_filter($results, fn($p) => $p['price'] <= $maxPrice);
}

if ($featured !== null) {
    $results = array_filter($results, fn($p) => $p['featured'] === $featured);
}

$results = array_values($results);

if ($sort !== null) {
    match ($sort) {
        'price_asc'  => usort($results, fn($a, $b) => $a['price'] <=> $b['price']),
        'price_desc' => usort($results, fn($a, $b) => $b['price'] <=> $a['price']),
        'name_asc'   => usort($results, fn($a, $b) => strcmp($a['name'], $b['name'])),
        'name_desc'  => usort($results, fn($a, $b) => strcmp($b['name'], $a['name'])),
        'stock_asc'  => usort($results, fn($a, $b) => $a['stock'] <=> $b['stock']),
        'stock_desc' => usort($results, fn($a, $b) => $b['stock'] <=> $a['stock']),
        default      => null,
    };
}

$total      = count($results);
$totalPages = max(1, (int) ceil($total / $perPage));
$offset     = ($page - 1) * $perPage;
$paginated  = array_slice($results, $offset, $perPage);

$response = [
    "success" => true,
    "data"    => $paginated,
    "meta"    => [
        "current_page" => $page,
        "per_page"     => $perPage,
        "total"        => $total,
        "total_pages"  => $totalPages,
        "has_next"     => $page < $totalPages,
        "has_prev"     => $page > 1,
    ],
    "filters_applied" => [
        "category"  => $category,
        "search"    => $search,
        "min_price" => $minPrice,
        "max_price" => $maxPrice,
        "sort"      => $sort,
        "featured"  => $featured,
    ],
    "errors" => null,
];

if ($total === 0) {
    $response["success"] = true;
}

http_response_code(200);
echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
