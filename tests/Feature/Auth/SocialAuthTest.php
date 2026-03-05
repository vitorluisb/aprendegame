<?php

use App\Models\User;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;

it('google oauth marks email as verified', function () {
    $socialUser = new SocialiteUser();
    $socialUser->map([
        'id' => 'google-id-123',
        'name' => 'Google User',
        'email' => 'google@user.com',
        'avatar' => 'https://avatar.url',
    ]);

    Socialite::shouldReceive('driver->user')->andReturn($socialUser);

    $this->get('/auth/google/callback')->assertRedirect('/dashboard');

    $user = User::query()->where('email', 'google@user.com')->firstOrFail();

    expect($user->email_verified_at)->not->toBeNull();
    expect($user->provider)->toBe('google');
    expect($user->studentProfile()->withoutGlobalScopes()->exists())->toBeTrue();
});
