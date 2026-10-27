<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SanctumAuthTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;
    private User $customerUser;
    private User $inactiveUser;
    private Category $category;
    private Brand $brand;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::create([
            'name' => 'Admin Boss',
            'email' => 'admin@test.com',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'is_active' => true,
        ]);

        $this->customerUser = User::create([
            'name' => 'Jane Customer',
            'email' => 'jane@test.com',
            'password' => Hash::make('password123'),
            'role' => 'customer',
            'is_active' => true,
        ]);

        $this->inactiveUser = User::create([
            'name' => 'Deactivated User',
            'email' => 'inactive@test.com',
            'password' => Hash::make('password123'),
            'role' => 'customer',
            'is_active' => false,
        ]);

        $this->category = Category::create([
            'name' => 'Smart Electronics',
            'slug' => 'smart-electronics',
            'is_active' => true,
        ]);

        $this->brand = Brand::create([
            'name' => 'NexusBrand',
            'slug' => 'nexusbrand',
            'is_active' => true,
        ]);
    }

    public function test_user_registration_succeeds_and_returns_sanctum_token(): void
    {
        $payload = [
            'name' => 'Alex Developer',
            'email' => 'alex@example.com',
            'password' => 'secretPassword123',
            'password_confirmation' => 'secretPassword123',
            'role' => 'customer',
        ];

        $response = $this->postJson('/api/v1/auth/register', $payload);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'User registered successfully.',
            ])
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'user' => ['id', 'name', 'email', 'role', 'is_active'],
                    'token',
                    'token_type',
                ],
                'errors',
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'alex@example.com',
            'name' => 'Alex Developer',
            'role' => 'customer',
            'is_active' => true,
        ]);
    }

    public function test_registration_validation_fails_on_duplicate_email_or_invalid_password(): void
    {
        $payload = [
            'name' => 'Jane Duplicate',
            'email' => 'jane@test.com', // Duplicate
            'password' => 'short',       // Less than 8 chars, no confirmation
        ];

        $response = $this->postJson('/api/v1/auth/register', $payload);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'The given data was invalid.',
            ])
            ->assertJsonValidationErrors(['email', 'password']);
    }

    public function test_user_login_succeeds_and_returns_sanctum_token(): void
    {
        $payload = [
            'email' => 'jane@test.com',
            'password' => 'password123',
        ];

        $response = $this->postJson('/api/v1/auth/login', $payload);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'user' => [
                        'email' => 'jane@test.com',
                        'role' => 'customer',
                    ],
                    'token_type' => 'Bearer',
                ],
            ]);

        $this->assertNotEmpty($response->json('data.token'));
    }

    public function test_login_fails_with_invalid_credentials(): void
    {
        $payload = [
            'email' => 'jane@test.com',
            'password' => 'wrong-password',
        ];

        $response = $this->postJson('/api/v1/auth/login', $payload);

        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
                'message' => 'Invalid email or password credentials.',
            ]);
    }

    public function test_deactivated_user_is_blocked_by_middleware_on_login(): void
    {
        $payload = [
            'email' => 'inactive@test.com',
            'password' => 'password123',
        ];

        $response = $this->postJson('/api/v1/auth/login', $payload);

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'Account is deactivated. Please contact support.',
            ]);
    }

    public function test_deactivated_user_token_is_blocked_by_ensure_customer_is_active_middleware(): void
    {
        $token = $this->inactiveUser->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/v1/auth/me');

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'Account is deactivated. Please contact support.',
            ]);
    }

    public function test_protected_routes_require_sanctum_bearer_token(): void
    {
        $response = $this->getJson('/api/v1/auth/me');

        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
                'message' => 'Unauthenticated: Bearer token is missing or invalid.',
            ]);
    }

    public function test_authenticated_user_can_retrieve_profile(): void
    {
        $token = $this->customerUser->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/v1/auth/me');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'user' => [
                        'email' => 'jane@test.com',
                        'name' => 'Jane Customer',
                        'role' => 'customer',
                        'is_active' => true,
                    ],
                ],
            ]);
    }

    public function test_token_refresh_revokes_old_token_and_issues_new_token(): void
    {
        $token = $this->customerUser->createToken('old-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/v1/auth/refresh');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Token refreshed successfully.',
            ]);

        $newToken = $response->json('data.token');
        $this->assertNotEmpty($newToken);
        $this->assertNotEquals($token, $newToken);

        // Reset auth guards to ensure subsequent request re-validates token against database
        app('auth')->forgetGuards();

        // Subsequent call with old token fails
        $retryResponse = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/v1/auth/me');
        $retryResponse->assertStatus(401);

        // Reset auth guards and test new token
        app('auth')->forgetGuards();

        // New token works
        $newResponse = $this->withHeader('Authorization', 'Bearer ' . $newToken)
            ->getJson('/api/v1/auth/me');
        $newResponse->assertStatus(200);
    }

    public function test_user_can_logout_and_revoke_current_token(): void
    {
        $token = $this->customerUser->createToken('logout-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/v1/auth/logout');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Successfully logged out.',
            ]);

        // Reset auth guards to verify token revocation against the database
        app('auth')->forgetGuards();

        // Attempting to access protected route with revoked token fails
        $subsequent = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/v1/auth/me');
        $subsequent->assertStatus(401);
    }

    public function test_customer_cannot_create_product_policy_gate(): void
    {
        $customerToken = $this->customerUser->createToken('customer-token')->plainTextToken;

        $payload = [
            'name' => 'Hacker Product',
            'sku' => 'HCK-PRD-001',
            'category_id' => $this->category->id,
            'brand_id' => $this->brand->id,
            'description' => 'Should be rejected by authorization policy.',
            'price' => 199.99,
            'stock_quantity' => 10,
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $customerToken)
            ->postJson('/api/v1/products', $payload);

        $response->assertStatus(403);
    }

    public function test_admin_can_create_product_policy_gate(): void
    {
        $adminToken = $this->adminUser->createToken('admin-token')->plainTextToken;

        $payload = [
            'name' => 'Authorized Admin Product',
            'sku' => 'ADM-PRD-001',
            'category_id' => $this->category->id,
            'brand_id' => $this->brand->id,
            'description' => 'Created by admin with policy clearance.',
            'price' => 299.99,
            'stock_quantity' => 50,
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $adminToken)
            ->postJson('/api/v1/products', $payload);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Product created successfully with gallery images.',
            ]);

        $this->assertDatabaseHas('products', [
            'sku' => 'ADM-PRD-001',
            'name' => 'Authorized Admin Product',
        ]);
    }
}
