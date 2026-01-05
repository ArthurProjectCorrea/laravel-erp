<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    #[\PHPUnit\Framework\Attributes\Test]
    public function login_with_valid_credentials_returns_token(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'is_active' => true,
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'token',
                'user' => [
                    'id',
                    'email',
                    'name',
                    'is_active',
                ],
            ]);

        $this->assertNotNull($response['token']);
        $this->assertEquals('Login successful', $response['message']);

        // Verify token was created in database
        $this->assertDatabaseHas('personal_access_tokens', [
            'tokenable_id' => $user->id,
            'tokenable_type' => User::class,
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function login_with_incorrect_password_fails(): void
    {
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('correct-password'),
            'is_active' => true,
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(422);
        $response->assertJsonStructure(['message']);

        // No token should be created
        $this->assertDatabaseMissing('personal_access_tokens', [
            'tokenable_type' => User::class,
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function login_with_nonexistent_email_fails(): void
    {
        $response = $this->postJson('/api/login', [
            'email' => 'nonexistent@example.com',
            'password' => 'password',
        ]);

        $response->assertStatus(422);
        $response->assertJsonStructure(['message']);

        $this->assertDatabaseMissing('personal_access_tokens', [
            'tokenable_type' => User::class,
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function login_with_inactive_user_fails(): void
    {
        User::factory()->create([
            'email' => 'inactive@example.com',
            'password' => bcrypt('password'),
            'is_active' => false,
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'inactive@example.com',
            'password' => 'password',
        ]);

        // Controller treats inactive users same as invalid credentials
        $response->assertStatus(422);

        // No token should be created
        $this->assertDatabaseMissing('personal_access_tokens', [
            'tokenable_type' => User::class,
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function login_respects_rate_limit(): void
    {
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'is_active' => true,
        ]);

        // Make 5 failed attempts (rate limit is 5 per minute)
        for ($i = 0; $i < 5; $i++) {
            $response = $this->postJson('/api/login', [
                'email' => 'test@example.com',
                'password' => 'wrong-password',
            ]);
            $response->assertStatus(422);
        }

        // 6th attempt should be rate limited
        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(429);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function login_requires_email_and_password(): void
    {
        $response = $this->postJson('/api/login', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email', 'password']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function login_with_invalid_email_format_fails(): void
    {
        $response = $this->postJson('/api/login', [
            'email' => 'invalid-email',
            'password' => 'password',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }
}
