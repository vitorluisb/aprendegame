<?php

use App\Domain\Accounts\Models\School;
use App\Domain\Accounts\Services\InviteService;
use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Notification;

it('registers user and sends verification email', function () {
    Notification::fake();

    $response = $this->post('/register', [
        'name' => 'Teste',
        'email' => 'teste@exemplo.com',
        'password' => 'Senha@Forte123',
        'password_confirmation' => 'Senha@Forte123',
    ]);

    $response->assertRedirect('/email/verify');

    $user = User::query()->where('email', 'teste@exemplo.com')->firstOrFail();
    Notification::assertSentTo($user, VerifyEmail::class);
    expect($user->studentProfile()->withoutGlobalScopes()->exists())->toBeTrue();
});

it('blocks unverified user from dashboard', function () {
    $user = User::factory()->unverified()->create();

    $this->actingAs($user)
        ->get('/dashboard')
        ->assertRedirect('/email/verify');
});

it('rejects weak password', function () {
    $this->post('/register', [
        'name' => 'Fraco',
        'email' => 'fraco@exemplo.com',
        'password' => '123456',
        'password_confirmation' => '123456',
    ])->assertSessionHasErrors('password');
});

it('rate limits login after 5 attempts', function () {
    for ($attempt = 0; $attempt < 6; $attempt++) {
        $this->post('/login', [
            'email' => 'x@x.com',
            'password' => 'wrong',
        ]);
    }

    $this->post('/login', [
        'email' => 'x@x.com',
        'password' => 'wrong',
    ])->assertStatus(429);
});

it('invite expires after 48 hours', function () {
    $service = app(InviteService::class);
    $school = School::factory()->create();

    $token = $service->invite('novo@prof.com', $school, 'teacher');

    Carbon::setTestNow(now()->addHours(49));

    $user = User::factory()->create(['email' => 'novo@prof.com']);

    expect(fn () => $service->accept($token, $user))
        ->toThrow(Exception::class, 'Convite inválido ou expirado');

    Carbon::setTestNow();
});
