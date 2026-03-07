<?php

use App\Models\User;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;

it('google oauth stores pending user in session and redirects to role selection', function () {
    $socialUser = new SocialiteUser;
    $socialUser->map([
        'id' => 'google-id-123',
        'name' => 'Google User',
        'email' => 'google@user.com',
        'avatar' => 'https://avatar.url',
    ]);

    Socialite::shouldReceive('driver->user')->andReturn($socialUser);

    $this->get('/auth/google/callback')
        ->assertRedirect('/selecionar-perfil')
        ->assertSessionHas('google_pending_user', [
            'name' => 'Google User',
            'email' => 'google@user.com',
            'provider_id' => 'google-id-123',
            'avatar_url' => 'https://avatar.url',
        ]);

    expect(User::query()->where('email', 'google@user.com')->exists())->toBeFalse();
});

it('google oauth logs in existing user and redirects to dashboard', function () {
    $user = User::factory()->create([
        'email' => 'google@user.com',
        'email_verified_at' => now(),
    ]);

    $socialUser = new SocialiteUser;
    $socialUser->map([
        'id' => 'google-id-123',
        'name' => 'Google User',
        'email' => 'google@user.com',
        'avatar' => 'https://avatar.url',
    ]);

    Socialite::shouldReceive('driver->user')->andReturn($socialUser);

    $this->get('/auth/google/callback')->assertRedirect('/dashboard');

    $user->refresh();
    expect($user->provider)->toBe('google');
    expect($user->provider_id)->toBe('google-id-123');
    expect($user->avatar_url)->toBe('https://avatar.url');
});
