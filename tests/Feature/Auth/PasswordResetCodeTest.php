<?php

use App\Models\User;
use App\Notifications\PasswordResetCodeNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;

beforeEach(function () {
    Notification::fake();
});

/*
|--------------------------------------------------------------------------
| Forgot Password - Send Code Tests
|--------------------------------------------------------------------------
*/

test('forgot password page can be rendered', function () {
    $response = $this->get('/forgot-password');

    $response->assertStatus(200);
});

test('reset code can be sent to valid email', function () {
    $user = User::factory()->create([
        'email' => 'test@example.com',
    ]);

    $response = $this->post('/forgot-password', [
        'email' => 'test@example.com',
    ]);

    $response->assertRedirect('/verify-code');
    $response->assertSessionHas('status');

    Notification::assertSentTo($user, PasswordResetCodeNotification::class);

    $this->assertDatabaseHas('password_reset_codes', [
        'email' => 'test@example.com',
        'verified' => false,
    ]);
});

test('reset code cannot be sent to invalid email', function () {
    $response = $this->post('/forgot-password', [
        'email' => 'nonexistent@example.com',
    ]);

    $response->assertSessionHasErrors('email');
    Notification::assertNothingSent();
});

test('reset code cannot be sent without email', function () {
    $response = $this->post('/forgot-password', [
        'email' => '',
    ]);

    $response->assertSessionHasErrors('email');
});

test('previous codes are deleted when requesting new code', function () {
    $user = User::factory()->create([
        'email' => 'test@example.com',
    ]);

    // First request
    $this->post('/forgot-password', ['email' => 'test@example.com']);

    // Second request
    $this->post('/forgot-password', ['email' => 'test@example.com']);

    // Should only have one code
    $this->assertDatabaseCount('password_reset_codes', 1);
});

/*
|--------------------------------------------------------------------------
| Verify Code Tests
|--------------------------------------------------------------------------
*/

test('verify code page can be rendered', function () {
    $response = $this->get('/verify-code');

    $response->assertStatus(200);
});

test('valid code can be verified', function () {
    $user = User::factory()->create([
        'email' => 'test@example.com',
    ]);

    $code = '123456';

    DB::table('password_reset_codes')->insert([
        'email' => 'test@example.com',
        'code' => Hash::make($code),
        'verified' => false,
        'expires_at' => now()->addMinutes(15),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $response = $this->post('/verify-code', [
        'email' => 'test@example.com',
        'code' => $code,
    ]);

    $response->assertRedirect('/reset-password');
    $response->assertSessionHas('status');

    $this->assertDatabaseHas('password_reset_codes', [
        'email' => 'test@example.com',
        'verified' => true,
    ]);
});

test('invalid code cannot be verified', function () {
    $user = User::factory()->create([
        'email' => 'test@example.com',
    ]);

    DB::table('password_reset_codes')->insert([
        'email' => 'test@example.com',
        'code' => Hash::make('123456'),
        'verified' => false,
        'expires_at' => now()->addMinutes(15),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $response = $this->post('/verify-code', [
        'email' => 'test@example.com',
        'code' => '000000',
    ]);

    $response->assertSessionHasErrors('code');

    $this->assertDatabaseHas('password_reset_codes', [
        'email' => 'test@example.com',
        'verified' => false,
    ]);
});

test('expired code cannot be verified', function () {
    $user = User::factory()->create([
        'email' => 'test@example.com',
    ]);

    DB::table('password_reset_codes')->insert([
        'email' => 'test@example.com',
        'code' => Hash::make('123456'),
        'verified' => false,
        'expires_at' => now()->subMinutes(1), // Expired
        'created_at' => now()->subMinutes(20),
        'updated_at' => now()->subMinutes(20),
    ]);

    $response = $this->post('/verify-code', [
        'email' => 'test@example.com',
        'code' => '123456',
    ]);

    $response->assertSessionHasErrors('code');

    // Code should be deleted
    $this->assertDatabaseMissing('password_reset_codes', [
        'email' => 'test@example.com',
    ]);
});

test('code verification requires valid email', function () {
    $response = $this->post('/verify-code', [
        'email' => 'nonexistent@example.com',
        'code' => '123456',
    ]);

    $response->assertSessionHasErrors('email');
});

test('code verification requires 6 digit code', function () {
    $user = User::factory()->create([
        'email' => 'test@example.com',
    ]);

    $response = $this->post('/verify-code', [
        'email' => 'test@example.com',
        'code' => '123', // Too short
    ]);

    $response->assertSessionHasErrors('code');
});

/*
|--------------------------------------------------------------------------
| Reset Password Tests
|--------------------------------------------------------------------------
*/

test('reset password page can be rendered', function () {
    $response = $this->get('/reset-password');

    $response->assertStatus(200);
});

test('password can be reset with valid verified code', function () {
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => Hash::make('old-password'),
    ]);

    $code = '123456';

    DB::table('password_reset_codes')->insert([
        'email' => 'test@example.com',
        'code' => Hash::make($code),
        'verified' => true,
        'expires_at' => now()->addMinutes(15),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $response = $this->post('/reset-password', [
        'email' => 'test@example.com',
        'code' => $code,
        'password' => 'new-password123',
        'password_confirmation' => 'new-password123',
    ]);

    $response->assertRedirect('/login');
    $response->assertSessionHas('status');

    // Code should be deleted
    $this->assertDatabaseMissing('password_reset_codes', [
        'email' => 'test@example.com',
    ]);

    // Password should be updated
    $user->refresh();
    $this->assertTrue(Hash::check('new-password123', $user->password));
});

test('password cannot be reset with unverified code', function () {
    $user = User::factory()->create([
        'email' => 'test@example.com',
    ]);

    $code = '123456';

    DB::table('password_reset_codes')->insert([
        'email' => 'test@example.com',
        'code' => Hash::make($code),
        'verified' => false, // Not verified
        'expires_at' => now()->addMinutes(15),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $response = $this->post('/reset-password', [
        'email' => 'test@example.com',
        'code' => $code,
        'password' => 'new-password123',
        'password_confirmation' => 'new-password123',
    ]);

    $response->assertSessionHasErrors('code');
});

test('password cannot be reset with invalid code', function () {
    $user = User::factory()->create([
        'email' => 'test@example.com',
    ]);

    DB::table('password_reset_codes')->insert([
        'email' => 'test@example.com',
        'code' => Hash::make('123456'),
        'verified' => true,
        'expires_at' => now()->addMinutes(15),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $response = $this->post('/reset-password', [
        'email' => 'test@example.com',
        'code' => '000000', // Wrong code
        'password' => 'new-password123',
        'password_confirmation' => 'new-password123',
    ]);

    $response->assertSessionHasErrors('code');
});

test('password reset requires confirmation', function () {
    $user = User::factory()->create([
        'email' => 'test@example.com',
    ]);

    $code = '123456';

    DB::table('password_reset_codes')->insert([
        'email' => 'test@example.com',
        'code' => Hash::make($code),
        'verified' => true,
        'expires_at' => now()->addMinutes(15),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $response = $this->post('/reset-password', [
        'email' => 'test@example.com',
        'code' => $code,
        'password' => 'new-password123',
        'password_confirmation' => 'different-password',
    ]);

    $response->assertSessionHasErrors('password');
});

test('password reset fails with expired session', function () {
    $user = User::factory()->create([
        'email' => 'test@example.com',
    ]);

    $code = '123456';

    DB::table('password_reset_codes')->insert([
        'email' => 'test@example.com',
        'code' => Hash::make($code),
        'verified' => true,
        'expires_at' => now()->subMinutes(1), // Expired
        'created_at' => now()->subMinutes(20),
        'updated_at' => now()->subMinutes(20),
    ]);

    $response = $this->post('/reset-password', [
        'email' => 'test@example.com',
        'code' => $code,
        'password' => 'new-password123',
        'password_confirmation' => 'new-password123',
    ]);

    $response->assertSessionHasErrors('code');
});

/*
|--------------------------------------------------------------------------
| Resend Code Tests
|--------------------------------------------------------------------------
*/

test('code can be resent', function () {
    $user = User::factory()->create([
        'email' => 'test@example.com',
    ]);

    // Create initial code
    DB::table('password_reset_codes')->insert([
        'email' => 'test@example.com',
        'code' => Hash::make('123456'),
        'verified' => false,
        'expires_at' => now()->addMinutes(15),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $response = $this->post('/resend-code', [
        'email' => 'test@example.com',
    ]);

    $response->assertRedirect('/verify-code');

    Notification::assertSentTo($user, PasswordResetCodeNotification::class);

    // Should still only have one code (old one deleted)
    $this->assertDatabaseCount('password_reset_codes', 1);
});

/*
|--------------------------------------------------------------------------
| Full Flow Integration Test
|--------------------------------------------------------------------------
*/

test('complete password reset flow works end to end', function () {
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => Hash::make('old-password'),
    ]);

    // Step 1: Request code
    $response = $this->post('/forgot-password', [
        'email' => 'test@example.com',
    ]);

    $response->assertRedirect('/verify-code');
    Notification::assertSentTo($user, PasswordResetCodeNotification::class);

    // Get the code from the notification
    $code = null;
    Notification::assertSentTo(
        $user,
        PasswordResetCodeNotification::class,
        function ($notification) use (&$code) {
            $code = $notification->code;

            return true;
        }
    );

    // Step 2: Verify code
    $response = $this->post('/verify-code', [
        'email' => 'test@example.com',
        'code' => $code,
    ]);

    $response->assertRedirect('/reset-password');

    // Step 3: Reset password
    $response = $this->post('/reset-password', [
        'email' => 'test@example.com',
        'code' => $code,
        'password' => 'new-secure-password123',
        'password_confirmation' => 'new-secure-password123',
    ]);

    $response->assertRedirect('/login');

    // Verify password was changed
    $user->refresh();
    $this->assertTrue(Hash::check('new-secure-password123', $user->password));
    $this->assertFalse(Hash::check('old-password', $user->password));

    // Verify code was deleted
    $this->assertDatabaseMissing('password_reset_codes', [
        'email' => 'test@example.com',
    ]);
});
