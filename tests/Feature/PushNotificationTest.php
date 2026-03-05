<?php

use App\Domain\Accounts\Services\PushNotificationService;
use App\Models\PushSubscription;
use App\Models\User;
use Mockery\MockInterface;

it('user can subscribe to push notifications', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->postJson('/push/subscriptions', [
            'endpoint' => 'https://fcm.googleapis.com/fcm/send/abc123',
            'p256dh' => base64_encode('fake-p256dh-key'),
            'auth' => base64_encode('fake-auth'),
        ])
        ->assertStatus(201);

    expect(PushSubscription::where('user_id', $user->id)->count())->toBe(1);
});

it('subscribing again with same endpoint updates existing record', function () {
    $user = User::factory()->create();
    $endpoint = 'https://fcm.googleapis.com/fcm/send/unique-endpoint';

    $this->actingAs($user)->postJson('/push/subscriptions', [
        'endpoint' => $endpoint,
        'p256dh' => base64_encode('key-v1'),
        'auth' => base64_encode('auth-v1'),
    ]);

    $this->actingAs($user)->postJson('/push/subscriptions', [
        'endpoint' => $endpoint,
        'p256dh' => base64_encode('key-v2'),
        'auth' => base64_encode('auth-v2'),
    ]);

    expect(PushSubscription::where('endpoint', $endpoint)->count())->toBe(1);
});

it('user can unsubscribe from push notifications', function () {
    $user = User::factory()->create();
    $sub = PushSubscription::factory()->create(['user_id' => $user->id]);

    $this->actingAs($user)
        ->deleteJson('/push/subscriptions', ['endpoint' => $sub->endpoint])
        ->assertNoContent();

    expect(PushSubscription::where('user_id', $user->id)->count())->toBe(0);
});

it('unauthenticated user cannot subscribe', function () {
    $this->postJson('/push/subscriptions', [
        'endpoint' => 'https://example.com/push/abc',
        'p256dh' => 'key',
        'auth' => 'auth',
    ])->assertUnauthorized();
});

it('vapid public key endpoint returns key', function () {
    config(['services.vapid.public_key' => 'test-vapid-public-key']);
    $user = User::factory()->create();

    $this->actingAs($user)
        ->getJson('/push/vapid-public-key')
        ->assertSuccessful()
        ->assertJsonFragment(['public_key' => 'test-vapid-public-key']);
});

it('subscribe validates required fields', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->postJson('/push/subscriptions', [])
        ->assertUnprocessable();
});

it('push subscription service subscribe method creates record', function () {
    $user = User::factory()->create();
    $service = app(PushNotificationService::class);

    $sub = $service->subscribe(
        $user,
        'https://fcm.googleapis.com/test-endpoint',
        base64_encode('p256dh'),
        base64_encode('auth'),
    );

    expect($sub->user_id)->toBe($user->id);
    expect($sub->endpoint)->toBe('https://fcm.googleapis.com/test-endpoint');
});

it('push subscription service unsubscribe removes record', function () {
    $user = User::factory()->create();
    $sub = PushSubscription::factory()->create(['user_id' => $user->id]);
    $service = app(PushNotificationService::class);

    $result = $service->unsubscribe($user, $sub->endpoint);

    expect($result)->toBeTrue();
    expect(PushSubscription::where('id', $sub->id)->exists())->toBeFalse();
});
