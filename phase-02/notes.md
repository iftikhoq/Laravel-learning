# Phase 2 Notes: Laravel Core, Architecture & Routing

**Date**: 2026-09-13  
**Milestone**: Phase 2 — Laravel 11 Core Framework, Directory Structure, Lifecycle, Routing & Controllers  
**Code Directory**: `laravel learning/phase-02/`

---

## 1. The Laravel 11 Request Lifecycle (Step-by-Step)

When an HTTP request enters a Laravel 11 application, it flows through a structured, predictable pipeline:

```
[HTTP Request from Client (Browser / React SPA / Postman)]
                       │
                       ▼
            public/index.php (Single Entry Point)
                       │
                       ▼
      bootstrap/app.php (App Initialization & Binding)
                       │
                       ▼
       Service Providers (Bootstrapping Core Services)
                       │
                       ▼
       Global & Route Middleware (Auth, CORS, Rate Limiting)
                       │
                       ▼
       Router (routes/web.php or routes/api.php)
                       │
                       ▼
           Controller (Invokes Domain Logic / Services)
                       │
                       ▼
     Response Execution (JSON Envelope / Blade View)
```

### Detailed Lifecycle Stages:
1. **Entry Point (`public/index.php`)**: Every web request hits `public/index.php` first. Nginx/Apache re-routes all incoming requests to this file. It loads Composer's `vendor/autoload.php`.
2. **Bootstrapping (`bootstrap/app.php`)**: In Laravel 11, `bootstrap/app.php` acts as the single configuration hub. It configures routing (`routes/web.php`, `routes/api.php`), registers global middleware, and configures exception handling.
3. **Service Providers (`app/Providers/AppServiceProvider.php`)**: Service providers are the central place to register bindings in the Laravel IoC Container, set up global model configurations, or register custom event listeners.
4. **Middleware Pipeline**: The request passes through global middleware (e.g., CORS handling, trimming strings, trust proxies) and route-specific middleware (e.g., `auth:sanctum`, `throttle:api`).
5. **Routing & Route Model Binding**: The Router matches the request URI and HTTP method to a route handler. If the route parameter uses Route Model Binding (e.g., `{product}`), Eloquent automatically resolves the model instance from the database before passing it to the controller.
6. **Controller & Action**: The controller handles the request, interacts with Eloquent models or services, and returns a response.
7. **HTTP Response**: The response is sent back through the middleware stack (allowing response headers modification) to the client as JSON or HTML.

> **Django Comparison**:
> - Django's entry point is `wsgi.py` or `asgi.py` loading `settings.py` and `urls.py`.
> - Django Middleware operates via `process_request` / `process_response` callables.
> - Laravel 11 consolidates framework configuration in `bootstrap/app.php` instead of multiple separate setting classes.

---

## 2. Exhaustive Folder Structure Walkthrough (Django vs. Laravel)

Laravel 11 features a streamlined, developer-friendly folder structure:

| Laravel 11 File / Folder | Primary Purpose & Usage | Equivalent Concept in Django |
| :--- | :--- | :--- |
| `app/Http/Controllers/` | Receives requests, calls models/services, returns HTTP responses | Django `views.py` (FBVs or CBVs in views module) |
| `app/Http/Requests/` | Form Validation classes with custom rules and authorization logic | Django `forms.py` or DRF `serializers.Serializer` validation |
| `app/Http/Resources/` | Transforms Eloquent models into formatted JSON API payloads | Django REST Framework `serializers.ModelSerializer` |
| `app/Http/Middleware/` | Request filtering (auth, rate limiting, CORS, headers) | Django `middleware.py` classes |
| `app/Models/` | Eloquent ORM models representing database tables | Django `models.Model` classes in `models.py` |
| `app/Providers/` | Bootstrapping core services, container bindings, model events | Django `apps.py` (`AppConfig.ready()`) |
| `bootstrap/app.php` | App setup, middleware registration, routing configuration | Django `wsgi.py` + `asgi.py` + `settings.py` middleware setup |
| `config/` | Config files (`app.php`, `database.php`, `cors.php`, `mail.php`) | Django `settings.py` |
| `database/migrations/` | Database schema version control definitions | Django `migrations/` directory (`0001_initial.py`) |
| `database/factories/` | Model factories using Faker for fake test data | `factory_boy` factory classes in Django |
| `database/seeders/` | Database seed scripts populating initial/test data | Django fixtures (`manage.py loaddata`) or custom seed scripts |
| `routes/web.php` | Web routes returning Blade views or HTML responses | Django `urls.py` (`urlpatterns`) for HTML views |
| `routes/api.php` | Stateless API routes returning JSON (`/api/...`) | Django `urls.py` under `/api/` route namespace |
| `storage/app/public/` | Uploaded media files (images, documents) | Django `MEDIA_ROOT` (`media/` directory) |
| `storage/logs/` | Daily and single application error logs | Django logging handler log outputs |
| `tests/Feature/` | Integration and HTTP endpoint test suites | Django `tests.py` using `django.test.TestCase` / `pytest` |
| `tests/Unit/` | Isolated PHP unit tests for pure classes/methods | Python `unittest` / `pytest` isolated unit tests |
| `artisan` | CLI executable script for Laravel tasks | `manage.py` in Django projects |
| `composer.json` | Project dependencies, scripts, and PSR-4 autoload rules | `pyproject.toml` / `requirements.txt` / `Pipfile` |
| `.env` & `.env.example` | Environment variables (DB credentials, secret keys) | `.env` / `django-environ` |

---

## 3. Configuration Architecture: `.env` vs `config/*.php`

### The Golden Rule of Laravel Configuration:
> **NEVER call `env()` outside files in the `config/` folder.**

#### Why?
In production, running `php artisan config:cache` compiles all configuration files into a single cached file (`bootstrap/cache/config.php`). Once config is cached, `env()` returns `null` everywhere outside of `config/` files!

#### Correct Pattern:
1. **`.env`** (Stores raw secret value):
   ```env
   APP_NAME="Modern E-Commerce"
   PRODUCT_PAGINATION_LIMIT=15
   ```

2. **`config/app.php`** (Reads `.env` with fallback):
   ```php
   'name' => env('APP_NAME', 'Laravel'),
   'pagination_limit' => env('PRODUCT_PAGINATION_LIMIT', 10),
   ```

3. **Controller / Service Code** (Accesses value via `config()` helper):
   ```php
   $limit = config('app.pagination_limit');
   ```

> **Django Comparison**: In Django, environment variables are loaded directly in `settings.py` via `os.environ` or `django-environ` and accessed via `from django.conf import settings`.

---

## 4. Artisan CLI Power Commands (Django vs. Laravel)

Laravel's `artisan` tool matches Django's `manage.py`:

| Task | Django Command | Laravel Artisan Command |
| :--- | :--- | :--- |
| Start Dev Server | `python manage.py runserver` | `php artisan serve` |
| Interactive Shell | `python manage.py shell` | `php artisan tinker` |
| List Routes | `python manage.py show_urls` | `php artisan route:list` |
| Create Controller | N/A (manually edit `views.py`) | `php artisan make:controller Api/v1/ProductController` |
| Create Model | N/A (manually edit `models.py`) | `php artisan make:model Product` |
| Create Model + Migration | N/A | `php artisan make:model Product -m` |
| Run Migrations | `python manage.py migrate` | `php artisan migrate` |
| Install API Scaffolding | N/A | `php artisan install:api` |
| Clear Config Cache | N/A | `php artisan config:clear` |

---

## 5. Routing Mechanics in Laravel 11

Routing connects incoming URL paths and HTTP methods to controller logic.

### 1. Basic Routes & Parameter Constraints
```php
use Illuminate\Support\Facades\Route;

// Simple GET route
Route::get('/health', function () {
    return response()->json(['status' => 'ok']);
});

// Route with parameter & regex constraint
Route::get('/products/{id}', function (string $id) {
    return response()->json(['product_id' => $id]);
})->where('id', '[0-9]+');
```

### 2. Route Groups & Prefixing
```php
use App\Http\Controllers\Api\v1\ProductController;
use App\Http\Controllers\Api\v1\CategoryController;

Route::prefix('v1')->name('api.v1.')->group(function () {
    Route::get('/products', [ProductController::class, 'index'])->name('products.index');
    Route::get('/products/{product}', [ProductController::class, 'show'])->name('products.show');
});
```

### 3. Route Model Binding (Laravel Magic)
Instead of accepting an integer `$id` and manually calling `Product::findOrFail($id)` in the controller, Laravel automatically fetches the database model instance matching the URL parameter name:

```php
// Route definition in routes/api.php
Route::get('/products/{product}', [ProductController::class, 'show']);

// Controller action in ProductController.php
public function show(Product $product): JsonResponse
{
    // $product is already an instantiated Product model matching the URL ID!
    // If not found, Laravel automatically throws an HTTP 404 response.
    return response()->json([
        'success' => true,
        'data' => $product
    ]);
}
```

> **Django Comparison**:
> - In Django: `path('products/<int:pk>/', ProductDetailView.as_view())`. The view explicitly retrieves the model using `get_object_or_404(Product, pk=pk)`.
> - In Laravel: Implicit Route Model Binding handles `404` resolution out-of-the-box simply by matching parameter name `{product}` with type hint `Product $product`.

---

## 6. Controllers & API Resource Controllers

Controllers organize HTTP request handling logic into dedicated classes.

### Controller Types:
1. **Invokable (Single Action) Controller**: Handles a single specialized task (`__invoke`).
   ```bash
   php artisan make:controller ProcessOrderController --invokable
   ```
2. **Resource Controller**: Contains standard RESTful actions (`index`, `store`, `show`, `update`, `destroy`).
   ```bash
   php artisan make:controller Api/v1/ProductController --api
   ```

### Standard API Resource Route Registration:
```php
Route::apiResource('products', ProductController::class);
```
This single line automatically registers 5 API endpoints:

| Verb | URI | Action | Route Name |
| :--- | :--- | :--- | :--- |
| `GET` | `/api/products` | `index` | `products.index` |
| `POST` | `/api/products` | `store` | `products.store` |
| `GET` | `/api/products/{product}` | `show` | `products.show` |
| `PUT`/`PATCH` | `/api/products/{product}` | `update` | `products.update` |
| `DELETE` | `/api/products/{product}` | `destroy` | `products.destroy` |

---

## 7. Deliverable Implementation Code Structure

In Phase 2 deliverable code (`laravel learning/phase-02/`):
- **Models**: `Category`, `Product` with mock data binding.
- **Controllers**:
  - `App\Http\Controllers\Api\v1\CategoryController`
  - `App\Http\Controllers\Api\v1\ProductController`
- **Routing**: `routes/api.php` defining `/api/v1/categories` and `/api/v1/products` endpoints.
- **Standard Envelope**: Response payloads follow the standardized structure:
  ```json
  {
    "success": true,
    "message": "Resource fetched successfully.",
    "data": { ... }
  }
  ```

---

*Phase 2 Notes compiled for Laravel Learning repository.*
