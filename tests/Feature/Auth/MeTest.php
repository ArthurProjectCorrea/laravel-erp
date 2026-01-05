<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MeTest extends TestCase
{
    use RefreshDatabase;

    #[\PHPUnit\Framework\Attributes\Test]
    public function me_with_valid_token_returns_user_data(): void
    {
        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'is_active' => true,
        ]);

        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->getJson('/api/me', [
            'Authorization' => 'Bearer '.$token,
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'user' => [
                    'id',
                    'name',
                    'email',
                    'is_active',
                    'created_at',
                    'updated_at',
                ],
            ]);

        $this->assertEquals($user->id, $response['user']['id']);
        $this->assertEquals('Test User', $response['user']['name']);
        $this->assertEquals('test@example.com', $response['user']['email']);
        $this->assertEquals(true, $response['user']['is_active']);

        // Should NOT return password
        $this->assertArrayNotHasKey('password', $response['user']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function me_without_token_fails(): void
    {
        $response = $this->getJson('/api/me');

        $response->assertStatus(401);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function me_with_invalid_token_fails(): void
    {
        $response = $this->getJson('/api/me', [
            'Authorization' => 'Bearer invalid-token',
        ]);

        $response->assertStatus(401);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function me_with_inactive_user_fails(): void
    {
        $user = User::factory()->create([
            'is_active' => true,
        ]);

        $token = $user->createToken('test-token')->plainTextToken;

        // Deactivate user
        $user->update(['is_active' => false]);

        $response = $this->getJson('/api/me', [
            'Authorization' => 'Bearer '.$token,
        ]);

        $response->assertStatus(403);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function me_with_revoked_token_fails(): void
    {
        $user = User::factory()->create([
            'is_active' => true,
        ]);

        $token = $user->createToken('test-token')->plainTextToken;

        // Revoke the token
        $user->tokens()->delete();

        $response = $this->getJson('/api/me', [
            'Authorization' => 'Bearer '.$token,
        ]);

        $response->assertStatus(401);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function me_returns_correct_user_data_after_update(): void
    {
        $user = User::factory()->create([
            'name' => 'Original Name',
            'email' => 'original@example.com',
            'is_active' => true,
        ]);

        $token = $user->createToken('test-token')->plainTextToken;

        // Update user data
        $user->update([
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
        ]);

        $response = $this->getJson('/api/me', [
            'Authorization' => 'Bearer '.$token,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'user' => [
                    'name' => 'Updated Name',
                    'email' => 'updated@example.com',
                ],
            ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function me_handles_multiple_tokens_correctly(): void
    {
        $user = User::factory()->create([
            'is_active' => true,
        ]);

        $token1 = $user->createToken('token-1')->plainTextToken;
        $token2 = $user->createToken('token-2')->plainTextToken;

        // Both tokens should return the same user
        $response1 = $this->getJson('/api/me', [
            'Authorization' => 'Bearer '.$token1,
        ]);

        $response2 = $this->getJson('/api/me', [
            'Authorization' => 'Bearer '.$token2,
        ]);

        $response1->assertStatus(200);
        $response2->assertStatus(200);

        $this->assertEquals($response1['user']['id'], $response2['user']['id']);
        $this->assertEquals($response1['user']['email'], $response2['user']['email']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function me_works_after_successful_login(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'is_active' => true,
        ]);

        // Login
        $loginResponse = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        $loginResponse->assertStatus(200);
        $token = $loginResponse['token'];

        // Get user info with token from login
        $response = $this->getJson('/api/me', [
            'Authorization' => 'Bearer '.$token,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'user' => [
                    'id' => $user->id,
                    'email' => 'test@example.com',
                ],
            ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function me_does_not_expose_sensitive_data(): void
    {
        $user = User::factory()->create([
            'is_active' => true,
        ]);

        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->getJson('/api/me', [
            'Authorization' => 'Bearer '.$token,
        ]);

        $response->assertStatus(200);

        $data = $response['user'];

        // Should not contain password or sensitive fields
        $this->assertArrayNotHasKey('password', $data);
        $this->assertArrayNotHasKey('remember_token', $data);
    }
}
