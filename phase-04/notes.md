# Phase 4 Notes: Forms, Validation & RESTful API Standardization

**Date**: 2026-10-04  
**Milestone**: Phase 4 — Form Requests, Storage Disks, Custom Validation Rules, API Resources & JSON Envelopes  
**Code Directory**: `laravel learning/phase-04/`

---

## 1. Form Requests vs. Django Forms & DRF Serializers

In both Django and Laravel, decoupling validation logic from controller/view handlers is essential for clean architecture.

```
Incoming HTTP Request (JSON / Multipart Form)
                      │
                      ▼
       [FormRequest: StoreProductRequest]
       ├── authorize(): Access Gate check (true / false ➔ 403)
       ├── rules(): Validation rules dictionary (➔ 422 on failure)
       └── messages(): Human-readable error overrides
                      │ (Valid)
                      ▼
     Controller Action: ProductController@store
     └── $validated = $request->validated();
```

### Architectural Comparison:

| Feature / Concept | Django REST Framework (DRF) | Laravel 11 FormRequest |
| :--- | :--- | :--- |
| **Validation Layer** | `serializers.Serializer` / `ModelSerializer` | `Illuminate\Foundation\Http\FormRequest` |
| **Execution Point** | Explicit: `serializer.is_valid(raise_exception=True)` in view | Implicit: Auto-injected & validated before controller action executes |
| **Failure Response** | HTTP 400 Bad Request with field error dictionary | HTTP 422 Unprocessable Content with standardized validation payload |
| **Authorization Gate** | View `permission_classes = [IsAuthenticated]` | Integrated in Request: `authorize(): bool` (returns 403 automatically) |
| **Extracted Data** | `serializer.validated_data` (Python dict) | `$request->validated()` (safe PHP array stripped of unvalidated fields) |

### Key Code Pattern (`StoreProductRequest`):
```php
public function authorize(): bool
{
    return true; // Or gate check: auth()->user()?->can('create', Product::class)
}

public function rules(): array
{
    return [
        'name' => ['required', 'string', 'max:255'],
        'category_id' => ['required', 'integer', 'exists:categories,id'],
        'brand_id' => ['required', 'integer', 'exists:brands,id'],
        'sku' => ['required', 'string', new ValidSku()],
        'price' => ['required', 'numeric', 'min:0'],
        'sale_price' => ['nullable', 'numeric', 'min:0', 'lt:price'],
        'stock_quantity' => ['required', 'integer', 'min:0'],
        'images' => ['nullable', 'array', 'max:5'],
        'images.*' => ['image', 'mimes:jpeg,png,webp,jpg', 'max:2048'],
    ];
}
```

---

## 2. Custom Validation Rules in Laravel 11 (`ValidSku`)

Laravel 11 uses the invokable `ValidationRule` contract (`Illuminate\Contracts\Validation\ValidationRule`).

### Why Custom Rules Matter:
While built-in rules handle primitive checks (`string`, `max`, `exists`), domain-specific business rules (such as inventory SKU syntax, IBAN validation, or promo code format) belong in encapsulated, testable Rule objects.

```php
namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\DB;

class ValidSku implements ValidationRule
{
    public function __construct(protected ?int $ignoreId = null) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // 1. Enforce uppercase alphanumeric hyphenated structure: PROD-CAT-01
        if (! preg_match('/^[A-Z0-9]{2,8}(-[A-Z0-9]{2,8})+$/', $value)) {
            $fail("The {$attribute} must follow standardized inventory format (e.g. PROD-CAT-01).");
            return;
        }

        // 2. Enforce uniqueness while respecting update ignoreId
        $query = DB::table('products')->where('sku', $value);
        if ($this->ignoreId !== null) {
            $query->where('id', '!=', $this->ignoreId);
        }

        if ($query->exists()) {
            $fail("The {$attribute} '{$value}' is already assigned to another inventory product.");
        }
    }
}
```

> **Comparison with Django**: In DRF, this is achieved by writing a validator function `validate_sku(self, value)` directly on the Serializer or as a standalone callable passed to `validators=[validate_sku]`. Laravel's standalone rule class is completely decoupled and reusable across any request or command.

---

## 3. Storage Disks, File Uploads & Public Symlinks

Laravel abstracts filesystem interactions through the Flysystem library using configurable **disks** defined in `config/filesystems.php`.

```
storage/app/public/products/sample.jpg   <── Actual physical file storage
               ▲
               │  Symlink: php artisan storage:link
               ▼
public/storage/products/sample.jpg        <── Web-accessible endpoint (https://domain/storage/...)
```

### The Three Built-in Disks:
1. `local`: Private files (`storage/app/private/`), inaccessible via web browser. Used for internal exports, invoices, or temporary zip files.
2. `public`: Publicly accessible media (`storage/app/public/`). Accessible to browsers via the symlink `public/storage`.
3. `s3`: Cloud object storage (AWS S3, Cloudflare R2, MinIO, Google Cloud Storage) with identical API calls.

### Uploading & Deleting Workflow:
```php
// Uploading an uploaded file instance
$path = Storage::disk('public')->putFile('products', $uploadedFile);
// Stored as: products/randomHash12345.webp

// Generating public asset URL
$url = Storage::disk('public')->url($path);
// Resolves to: http://localhost:8000/storage/products/randomHash12345.webp

// Deleting file when record is removed
if (Storage::disk('public')->exists($path)) {
    Storage::disk('public')->delete($path);
}
```

> **Django Comparison**:
> In Django, `models.ImageField(upload_to='products/')` handles upload automatically with `default_storage` configured in `settings.py`. In Laravel, file upload is explicitly handled in the controller or domain service with `Storage::disk('public')->putFile()`, providing explicit control over transactions, thumbnail generation, and sorting metadata.

---

## 4. API Resources & Standardized JSON Envelopes

API Resources (`JsonResource` and `ResourceCollection`) act as an abstraction layer between database Eloquent models and the JSON response consumed by frontend SPAs or mobile clients.

### Benefits:
1. **Prevents Leaking Database Columns**: Keeps internal database table structure, sensitive fields (`password`, `remember_token`, internal timestamps) hidden.
2. **Conditional Relationships (`whenLoaded`)**: Prevents N+1 database queries when transforming relations. If the controller eager-loaded `category`, it is included in the payload; otherwise, it is omitted cleanly.
3. **Consistent Field Formatting**: Enforces type casting (floats for prices, booleans for flags, ISO-8601 strings for dates).

### Standardized JSON Envelope Format:
Every API endpoint in the platform adheres to a standardized response envelope:

#### Collection / Paginated Response:
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Wireless ANC Headphones",
      "sku": "AUD-WNC-01",
      "price": 299.99,
      "sale_price": 249.99,
      "stock_quantity": 50,
      "is_active": true,
      "is_featured": true,
      "category": { "id": 1, "name": "Audio" },
      "brand": { "id": 1, "name": "SonicAudio" },
      "images": [
        {
          "id": 1,
          "image_url": "products/abc.jpg",
          "url": "http://localhost:8000/storage/products/abc.jpg",
          "is_primary": true,
          "sort_order": 1
        }
      ]
    }
  ],
  "meta": {
    "current_page": 1,
    "per_page": 15,
    "total": 45,
    "last_page": 3,
    "from": 1,
    "to": 15
  },
  "errors": null
}
```

#### Validation Error Response (HTTP 422):
```json
{
  "success": false,
  "message": "The given data was invalid.",
  "errors": {
    "sku": ["The sku must follow the standardized inventory format (e.g., PROD-ITEM-01)."],
    "price": ["The price field is required."]
  }
}
```

---

## 5. Pagination Strategies (Laravel vs. Django REST Framework)

| Strategy | Laravel Eloquent Method | DRF Equivalent | SQL Mechanism | Best Use Case |
| :--- | :--- | :--- | :--- | :--- |
| **Length-Aware** | `$query->paginate(15)` | `PageNumberPagination` | `SELECT COUNT(*)` + `LIMIT 15 OFFSET 30` | Traditional numbered pagination UI (`1, 2, 3 ... 10`) |
| **Simple** | `$query->simplePaginate(15)` | Custom Limit/Offset | `LIMIT 16 OFFSET 30` (No `COUNT(*)`) | Next/Previous mobile feeds, saving expensive count queries |
| **Cursor** | `$query->cursorPaginate(15)` | `CursorPagination` | `WHERE id > :last_id LIMIT 15` (Index Seek) | High-volume infinite scroll, real-time feeds with zero offset drift |

---

## 6. Centralized API Exception Handling in Laravel 11

In Laravel 11, application exception handling is centralized in `bootstrap/app.php`:

```php
->withExceptions(function (Exceptions $exceptions): void {
    $exceptions->shouldRenderJsonWhen(
        fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
    );

    $exceptions->render(function (ValidationException $e, Request $request) {
        if ($request->is('api/*') || $request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'The given data was invalid.',
                'errors' => $e->errors(),
            ], 422);
        }
    });

    $exceptions->render(function (NotFoundHttpException $e, Request $request) {
        if ($request->is('api/*') || $request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'Requested resource not found.',
                'errors' => null,
            ], 404);
        }
    });
})
```

---

## 7. Verification & Automated Test Results

Automated test suite executes via PHPUnit testing the entire lifecycle:
- Unit: Custom SKU pattern validation, regex boundary rejection, database uniqueness check with update ignore.
- Feature: FormRequest 422 interceptor, multi-image uploads to public storage disk, URL generation, eager-loaded single product view, dynamic pagination envelope, product update, image cascading deletion, and 404 error envelope.

```bash
php vendor/phpunit/phpunit/phpunit
# Result: 20 passed, 20 tests, 101 assertions (100% green)
```
