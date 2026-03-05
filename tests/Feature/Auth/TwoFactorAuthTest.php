<?php

use App\Models\User;
use Laravel\Fortify\Features;

it('two factor authentication feature is enabled', function () {
    expect(Features::enabled(Features::twoFactorAuthentication()))->toBeTrue();
});

it('user model has two factor authenticatable trait', function () {
    $traits = class_uses_recursive(User::class);
    expect($traits)->toContain(\Laravel\Fortify\TwoFactorAuthenticatable::class);
});

it('user can enable two factor authentication after confirming password', function () {
    $user = User::factory()->create(['role' => 'super_admin']);

    // Fortify requires recently confirmed password in session
    $this->actingAs($user)
        ->withSession(['auth.password_confirmed_at' => time()])
        ->post('/user/two-factor-authentication')
        ->assertRedirect();

    expect($user->fresh()->two_factor_secret)->not->toBeNull();
});

it('login redirects to two factor challenge when 2fa is fully confirmed', function () {
    $user = User::factory()->create(['role' => 'super_admin']);

    // Simulate user with 2FA already enabled and confirmed
    $user->forceFill([
        'two_factor_secret' => encrypt('fakesecret'),
        'two_factor_confirmed_at' => now(),
    ])->save();

    $this->post('/logout');

    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $response->assertRedirect('/two-factor-challenge');
});

it('two factor challenge view renders correctly', function () {
    $user = User::factory()->create(['role' => 'super_admin']);

    $user->forceFill([
        'two_factor_secret' => encrypt('fakesecret'),
        'two_factor_confirmed_at' => now(),
    ])->save();

    $this->post('/login', ['email' => $user->email, 'password' => 'password']);

    $this->get('/two-factor-challenge')->assertSuccessful()->assertSee('TwoFactorChallenge');
});

it('user can disable two factor authentication', function () {
    $user = User::factory()->create(['role' => 'super_admin']);

    // Enable first
    $this->actingAs($user)
        ->withSession(['auth.password_confirmed_at' => time()])
        ->post('/user/two-factor-authentication');

    expect($user->fresh()->two_factor_secret)->not->toBeNull();

    // Disable
    $this->actingAs($user)
        ->withSession(['auth.password_confirmed_at' => time()])
        ->delete('/user/two-factor-authentication');

    expect($user->fresh()->two_factor_secret)->toBeNull();
});
