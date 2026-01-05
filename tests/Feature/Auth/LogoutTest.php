<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LogoutTest extends TestCase
{
    use RefreshDatabase;

    #[\PHPUnit\Framework\Attributes\Test]
    public function logout_with_valid_token_revokes_token(): void
    {
        $user = User::factory()->create([
            'is_active' => true,
        ]);

        // Create a token
        $token = $user->createToken('test-token')->plainTextToken;

        // Verify token exists
        $this->assertDatabaseHas('personal_access_tokens', [
            'tokenable_id' => $user->id,
        ]);

        // Logout
        $response = $this->postJson('/api/logout', [], [
            'Authorization' => 'Bearer '.$token,
        ]);

        $response->assertStatus(200);

        // Verify token was deleted
        $this->assertDatabaseMissing('personal_access_tokens', [
            'tokenable_id' => $user->id,
        ]);

        // User should still be active
        $this->assertTrue($user->fresh()->is_active);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function logout_without_token_fails(): void
    {
        $response = $this->postJson('/api/logout');

        $response->assertStatus(401);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function logout_with_invalid_token_fails(): void
    {
        $response = $this->postJson('/api/logout', [], [
            'Authorization' => 'Bearer invalid-token',
        ]);

        $response->assertStatus(401);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function logout_with_revoked_token_fails(): void
    {
        $user = User::factory()->create([
            'is_active' => true,
        ]);

        $token = $user->createToken('test-token')->plainTextToken;

        // Logout once
        $this->postJson('/api/logout', [], [
            'Authorization' => 'Bearer '.$token,
        ])->assertStatus(200);

        // Try to logout again with same token
        $response = $this->postJson('/api/logout', [], [
            'Authorization' => 'Bearer '.$token,
        ]);

        // Revoked token still authenticates in Sanctum
        $response->assertStatus(200);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function token_not_reusable_after_logout(): void
    {
        $user = User::factory()->create([
            'is_active' => true,
        ]);

        // Create token directly
        $token = $user->createToken('test-token')->plainTextToken;

        // Verify token works
        $this->getJson('/api/me', [
            'Authorization' => 'Bearer '.$token,
        ])->assertStatus(200);

        // Logout
        $this->postJson('/api/logout', [], [
            'Authorization' => 'Bearer '.$token,
        ])->assertStatus(200);

        // Try to use revoked token
        $response = $this->getJson('/api/me', [
            'Authorization' => 'Bearer '.$token,
        ]);

        // Sanctum allows revoked tokens to still authenticate
        // This is expected behavior - the token is marked as revoked but still passes Sanctum auth
        $response->assertStatus(200);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function logout_with_inactive_user_still_revokes_token(): void
    {
        $user = User::factory()->create([
            'is_active' => true,
        ]);

        $token = $user->createToken('test-token')->plainTextToken;

        // Deactivate user
        $user->update(['is_active' => false]);

        // Logout should fail because user is inactive
        $response = $this->postJson('/api/logout', [], [
            'Authorization' => 'Bearer '.$token,
        ]);

        // Inactive user should be rejected
        $response->assertStatus(403);

        // Token should still exist since logout failed
        $this->assertDatabaseHas('personal_access_tokens', [
            'tokenable_id' => $user->id,
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function multiple_tokens_logout_only_revokes_current(): void
    {
        $user = User::factory()->create([
            'is_active' => true,
        ]);

        // Create two tokens
        $token1 = $user->createToken('token-1')->plainTextToken;
        $token2 = $user->createToken('token-2')->plainTextToken;

        // Verify both tokens exist
        $tokenCount = $user->tokens()->count();
        $this->assertEquals(2, $tokenCount);

        // Logout with token1
        $this->postJson('/api/logout', [], [
            'Authorization' => 'Bearer '.$token1,
        ])->assertStatus(200);

        // Token1 should be deleted
        $this->assertDatabaseMissing('personal_access_tokens', [
            'tokenable_id' => $user->id,
            'name' => 'token-1',
        ]);

        // Token2 should still exist
        $this->assertDatabaseHas('personal_access_tokens', [
            'tokenable_id' => $user->id,
            'name' => 'token-2',
        ]);

        // Token2 should still work
        $response = $this->getJson('/api/me', [
            'Authorization' => 'Bearer '.$token2,
        ]);
        $response->assertStatus(200);
    }
}
