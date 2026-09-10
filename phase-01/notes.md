# Phase 1 Notes: Web Fundamentals & PHP Basics

**Date**: 2026-09-01  
**PDF Phases Covered**: Phase 0 (Web Fundamentals), Phase 2 (PHP Fundamentals)

---

## 1. How the Web Works (HTTP Lifecycle)

Every time a user visits a website, this happens:

```
Browser (Client)                         Server (PHP/Laravel)
      |                                        |
      |-- 1. HTTP Request (GET /products) ---->|
      |                                        |-- 2. Server receives request
      |                                        |-- 3. PHP processes the logic
      |                                        |-- 4. Queries database (if needed)
      |<-- 5. HTTP Response (JSON/HTML) -------|
      |                                        |
      |-- 6. Browser renders the response      |
```

**Key takeaway**: The client NEVER talks to the database directly. The server is always the middleman. This is why we build APIs — so the React frontend (client) talks to the Laravel backend (server) over HTTP.

### HTTP Methods (verbs)

| Method | Purpose | Example | Django Equivalent |
| :--- | :--- | :--- | :--- |
| `GET` | Read / Fetch data | Get product list | `request.method == 'GET'` |
| `POST` | Create new data | Place an order | `request.method == 'POST'` |
| `PUT` / `PATCH` | Update existing data | Edit product price | DRF `.update()` / `.partial_update()` |
| `DELETE` | Remove data | Delete a review | DRF `.destroy()` |

### HTTP Status Codes (the ones you'll use constantly)

| Code | Name | When to use |
| :--- | :--- | :--- |
| `200` | OK | Successful GET, PUT, PATCH |
| `201` | Created | Successful POST (new resource) |
| `204` | No Content | Successful DELETE (nothing to return) |
| `400` | Bad Request | Invalid input that isn't field-level validation |
| `401` | Unauthorized | Not logged in (no token / expired token) |
| `403` | Forbidden | Logged in but wrong role (customer hitting admin endpoint) |
| `404` | Not Found | Resource doesn't exist |
| `422` | Unprocessable Entity | Validation errors (Laravel's default for FormRequest failures) |
| `429` | Too Many Requests | Rate limited |
| `500` | Internal Server Error | Bug on the server side |

> **Django comparison**: Django returns `400` for validation errors by default. Laravel returns `422` — this is important when wiring React error handling.

---

## 2. PHP Basics (vs Python/JS)

### Syntax Ground Rules

| Rule | PHP | Python | JavaScript |
| :--- | :--- | :--- | :--- |
| Statement terminator | `;` (semicolon required) | None (newline) | `;` (optional but used) |
| Variable prefix | `$` (always) | None | `let` / `const` / `var` |
| String concat | `.` (dot) | `+` or f-string | `+` or template literal |
| File start | `<?php` | None | None |
| Comments | `//`, `#`, `/* */` | `#`, `"""` | `//`, `/* */` |
| Print | `echo` / `print` | `print()` | `console.log()` |
| Debug | `var_dump()` | `print(repr())` | `console.dir()` |

### Variables

```php
$name = "Ifti";              // String — always use $
$price = 2499.99;            // Float
$stock = 150;                // Integer
$isAvailable = true;         // Boolean
$discount = null;            // Null
```

**Key difference from Python**: PHP has NO `let`, `const`, or `var`. Every variable is just `$variableName`. Constants use `const SHOP_NAME = "MyStore"` (no dollar sign).

### Type Checking & Debugging

```php
gettype($price);       // Returns: "double" (PHP calls floats "double")
var_dump($price);      // Returns: float(2499.99) — type + value
isset($discount);      // Returns: false (because $discount is null)
empty($discount);      // Returns: true (null, 0, "", false, [] are all "empty")
```

> **`var_dump()` is your best friend** — use it like Python's `print(repr(x))`. It shows the type AND value, which is critical when debugging type juggling issues.

### Type Juggling (⚠️ the PHP gotcha)

PHP automatically converts types during operations. This can be surprising:

```php
'5' + 3         // 8      — string '5' becomes int 5
true + true     // 2      — booleans become 1
'hello' + 5     // 5      — non-numeric string becomes 0
'' == false     // true    — empty string is "falsy"
0 == 'hello'    // true in PHP 7, false in PHP 8 — THIS was a famous bug source
```

**Lesson**: Always use `===` (strict comparison) instead of `==` (loose comparison). Laravel enforces this convention everywhere.

---

## 3. Arrays (the PHP Swiss Army Knife)

PHP arrays replace Python's `list`, `dict`, `tuple`, and `set` — they do everything.

### Indexed Arrays (like Python lists)

```php
$categories = ["Electronics", "Furniture", "Clothing"];
echo $categories[0];            // "Electronics"
$categories[] = "Kitchen";      // Append (like .append())
count($categories);             // 4 (like Python's len())
in_array("Furniture", $categories);  // true (like "in" operator)
```

### Associative Arrays (like Python dicts)

```php
$product = [
    "id" => 1,
    "name" => "Aluminium Sheet",
    "price" => 2499.99,
];

echo $product["name"];                    // "Aluminium Sheet"
array_key_exists("sku", $product);        // false
$product["sku"] = "ALU-SHEET-4X8";        // Add new key
```

> **This is the most important data structure in PHP/Laravel**. API responses, database rows, form data — everything is an associative array.

### Key Array Functions

| PHP | Python Equivalent | What it does |
| :--- | :--- | :--- |
| `count($arr)` | `len(arr)` | Length |
| `array_push($arr, $val)` | `arr.append(val)` | Add to end |
| `array_pop($arr)` | `arr.pop()` | Remove from end |
| `in_array($val, $arr)` | `val in arr` | Check existence |
| `array_key_exists($key, $arr)` | `key in dict` | Check key exists |
| `array_map(fn, $arr)` | `map(fn, arr)` | Transform each element |
| `array_filter($arr, fn)` | `filter(fn, arr)` | Keep matching elements |
| `array_slice($arr, 0, 3)` | `arr[0:3]` | Slice |
| `implode(", ", $arr)` | `", ".join(arr)` | Join to string |
| `explode(",", $str)` | `str.split(",")` | Split string to array |
| `sort($arr)` | `arr.sort()` | Sort in-place |
| `array_values($arr)` | `list(dict.values())` | Re-index |

---

## 4. Control Flow

### if / elseif / else

```php
if ($stock <= 0) {
    $status = "Out of Stock";
} elseif ($stock <= 10) {       // "elseif" is one word in PHP (not "else if")
    $status = "Low Stock";
} else {
    $status = "In Stock";
}
```

### Null Coalescing (`??`) — use this EVERYWHERE

```php
$discount = $data['discount'] ?? 0;       // If null/undefined, use 0
$name = $_GET['name'] ?? "Guest";          // Safe GET param access
$discount ??= 10;                          // Assign only if currently null
```

> **Django equivalent**: `request.GET.get('name', 'Guest')` — PHP's `??` does the same thing but is more versatile.

### match Expression (PHP 8.0+) — the clean switch

```php
$statusLabel = match ($order->status) {
    "pending"    => "⏳ Pending",
    "processing" => "🔄 Processing",
    "shipped"    => "📦 Shipped",
    "delivered"  => "✅ Delivered",
    default      => "❓ Unknown",
};
```

**Why use `match` over `switch`**: No `break` needed, returns a value, uses strict `===` comparison, and throws `UnhandledMatchError` if no case matches (fail-fast).

### foreach (the loop you'll use 90% of the time)

```php
// Loop through products (like Python's "for product in products:")
foreach ($products as $product) {
    echo $product["name"];
}

// With index/key (like Python's "for i, product in enumerate(products):")
foreach ($products as $index => $product) {
    echo "[{$index}] {$product['name']}";
}

// Through associative array (like Python's "for key, value in dict.items():")
foreach ($product as $key => $value) {
    echo "{$key}: {$value}";
}
```

---

## 5. Functions

### Basic function with type declarations

```php
function formatPrice(float $amount, string $currency = "৳"): string
{
    return $currency . number_format($amount, 2);
}
```

> **Type declarations are optional but critical**. Laravel uses them everywhere. They catch bugs at runtime instead of letting wrong types silently propagate (like Python without mypy).

### Nullable types (`?type`)

```php
function findProduct(?int $id): ?array     // Accepts null, can return null
{
    if ($id === null) return null;
    // ...lookup logic...
}
```

### Arrow functions (single expression)

```php
$addTax = fn(float $price): float => $price * 1.05;
```

### Closures (multi-line anonymous functions)

```php
$taxRate = 0.05;

$calculateTotal = function (array $items) use ($taxRate): float {
    // Must use "use ($var)" to access outer scope — PHP doesn't auto-capture
    $subtotal = array_sum(array_map(fn($item) => $item['price'] * $item['qty'], $items));
    return $subtotal * (1 + $taxRate);
};
```

> **Critical difference from JS/Python**: PHP closures do NOT automatically capture variables from the parent scope. You MUST use `use ($var)` to bring them in. Arrow functions (`fn() =>`) DO auto-capture (read-only).

### Named arguments (PHP 8.0+)

```php
$product = createProduct(
    name: "Copper Wire",
    price: 1200.00,
    isFeatured: true,     // Can skip parameters with defaults
    sku: "COP-WIRE-25"
);
```

---

## 6. String Functions (daily drivers)

| Function | What it does | Example |
| :--- | :--- | :--- |
| `trim($str)` | Remove whitespace from both ends | `trim("  hello  ")` → `"hello"` |
| `strtolower($str)` | Lowercase | `strtolower("ABC")` → `"abc"` |
| `strtoupper($str)` | Uppercase | `strtoupper("abc")` → `"ABC"` |
| `ucwords($str)` | Capitalize each word | `ucwords("hello world")` → `"Hello World"` |
| `str_contains($hay, $needle)` | Check substring (PHP 8) | `str_contains("hello", "ell")` → `true` |
| `str_starts_with($str, $prefix)` | Check prefix (PHP 8) | `str_starts_with("hello", "he")` → `true` |
| `str_replace($find, $replace, $str)` | Replace | `str_replace("a", "b", "cat")` → `"cbt"` |
| `explode(",", $str)` | Split → array | `explode(",", "a,b,c")` → `["a","b","c"]` |
| `implode(", ", $arr)` | Join array → string | `implode(", ", ["a","b"])` → `"a, b"` |
| `substr($str, 0, 5)` | Substring | `substr("hello world", 0, 5)` → `"hello"` |
| `strlen($str)` | Length | `strlen("hello")` → `5` |
| `number_format($num, 2)` | Format number | `number_format(1234.5, 2)` → `"1,234.50"` |

---

## 7. The Product API Pattern (Phase 1 Deliverable)

The final script (`07_product_api.php`) demonstrates the pattern that EVERY Laravel controller follows:

```
1. Receive HTTP Request
2. Extract parameters (filters, pagination)
3. Query/filter data
4. Sort results
5. Paginate
6. Wrap in standardized JSON envelope
7. Send HTTP Response
```

### JSON Envelope Format

```json
{
    "success": true,
    "data": [...],
    "meta": {
        "current_page": 1,
        "per_page": 10,
        "total": 42,
        "total_pages": 5,
        "has_next": true,
        "has_prev": false
    },
    "errors": null
}
```

> This envelope format is what we'll standardize across our entire E-Commerce API. In Laravel, this will be handled by API Resources and a response trait — but today we built it by hand to understand the mechanics.

---

## 8. PHP Operators Quick Reference

| Operator | Name | Example | Result |
| :--- | :--- | :--- | :--- |
| `??` | Null coalescing | `$x ?? 'default'` | Returns `$x` if not null, else `'default'` |
| `??=` | Null coalescing assignment | `$x ??= 10` | Sets `$x` to 10 only if currently null |
| `?:` | Elvis (short ternary) | `$x ?: 'default'` | Returns `$x` if truthy, else `'default'` |
| `<=>` | Spaceship | `1 <=> 2` | Returns -1, 0, or 1 (for sorting) |
| `===` | Strict equality | `0 === false` | `false` (different types) |
| `==` | Loose equality | `0 == false` | `true` (type juggled) ⚠️ |
| `...` | Spread/rest | `[...$a, ...$b]` | Merge arrays |
| `->` | Object property access | `$user->name` | Like Python's `user.name` |
| `=>` | Array key-value separator | `['key' => 'value']` | Like Python's `{'key': 'value'}` |
| `::` | Static method/constant | `User::find(1)` | Like Python's `User.objects.get(pk=1)` |

---

## Practice Scripts Reference

| Script | File | Run Command |
| :--- | :--- | :--- |
| Hello World & basics | `laravel learning/phase-01/01_hello.php` | `php "laravel learning/phase-01/01_hello.php"` |
| Variables & types | `laravel learning/phase-01/02_variables_types.php` | `php "laravel learning/phase-01/02_variables_types.php"` |
| Arrays | `laravel learning/phase-01/03_arrays.php` | `php "laravel learning/phase-01/03_arrays.php"` |
| Control flow | `laravel learning/phase-01/04_control_flow.php` | `php "laravel learning/phase-01/04_control_flow.php"` |
| Functions & strings | `laravel learning/phase-01/05_functions_strings.php` | `php "laravel learning/phase-01/05_functions_strings.php"` |
| HTTP lifecycle | `laravel learning/phase-01/06_http_lifecycle.php` | `php -S localhost:8000 "laravel learning/phase-01/06_http_lifecycle.php"` |
| Product API (deliverable) | `laravel learning/phase-01/07_product_api.php` | `php -S localhost:8080 "laravel learning/phase-01/07_product_api.php"` |

---

## References

- [PHP Official Docs — Language Reference](https://www.php.net/manual/en/langref.php)
- [PHP 8.0 Named Arguments](https://www.php.net/manual/en/functions.arguments.php#functions.named-arguments)
- [PHP 8.0 Match Expression](https://www.php.net/manual/en/control-structures.match.php)
- [PHP 8.1 Readonly Properties](https://www.php.net/manual/en/language.oop5.properties.php#language.oop5.properties.readonly-properties)
- [PHP Type Declarations](https://www.php.net/manual/en/language.types.declarations.php)
- [HTTP Status Codes — MDN](https://developer.mozilla.org/en-US/docs/Web/HTTP/Status)
- [Laravel HTTP Responses](https://laravel.com/docs/11.x/responses)
