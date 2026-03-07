<?php

use App\Domain\Accounts\Models\Student;
use App\Domain\AI\Models\TutorMessage;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Inertia\Testing\AssertableInertia as Assert;

it('student can view tutor chat page', function () {
    $user = User::factory()->create(['role' => 'student', 'school_id' => null]);
    $student = Student::factory()->create(['user_id' => $user->id, 'school_id' => null]);

    TutorMessage::factory()->create([
        'student_id' => $student->id,
        'role' => 'student',
        'content' => 'Me ajuda com frações',
        'blocked' => false,
    ]);

    $this->actingAs($user)
        ->get('/tutor')
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Tutor/Index')
            ->has('messages', 1)
            ->where('messages.0.role', 'student')
            ->where('remaining_messages', 14)
        );
});

it('user with student profile can access tutor page with non-student role', function () {
    $user = User::factory()->create(['role' => 'teacher', 'school_id' => null]);
    Student::factory()->create(['user_id' => $user->id, 'school_id' => null]);

    $this->actingAs($user)
        ->get('/tutor')
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Tutor/Index')
        );
});

it('student can send a valid tutor message and receive response', function () {
    config()->set('services.ai.url', '');
    config()->set('services.ai.key', '');

    $user = User::factory()->create(['role' => 'student', 'school_id' => null]);
    $student = Student::factory()->create(['user_id' => $user->id, 'school_id' => null]);

    $this->actingAs($user)
        ->from('/tutor')
        ->post('/tutor/mensagens', ['message' => 'Me explica regra de três'])
        ->assertRedirect('/tutor');

    expect(TutorMessage::query()->where('student_id', $student->id)->count())->toBe(2);

    $messages = TutorMessage::query()
        ->where('student_id', $student->id)
        ->orderBy('id')
        ->get();

    expect($messages[0]->role)->toBe('student');
    expect($messages[0]->blocked)->toBeFalse();
    expect($messages[1]->role)->toBe('tutor');
    expect($messages[1]->content)->toContain('Vamos por partes');
});

it('tutor uses ai provider response when configured', function () {
    config()->set('services.ai.url', 'https://openrouter.ai/api/v1/chat/completions');
    config()->set('services.ai.key', 'test-key');

    Http::fake([
        '*' => Http::response([
            'choices' => [
                [
                    'message' => [
                        'content' => 'Explicando passo a passo com exemplo simples.',
                    ],
                ],
            ],
            'usage' => [
                'prompt_tokens' => 42,
                'completion_tokens' => 84,
            ],
        ], 200),
    ]);

    $user = User::factory()->create(['role' => 'student', 'school_id' => null]);
    $student = Student::factory()->create(['user_id' => $user->id, 'school_id' => null]);

    $this->actingAs($user)
        ->from('/tutor')
        ->post('/tutor/mensagens', ['message' => 'Me ajuda com divisão'])
        ->assertRedirect('/tutor');

    $messages = TutorMessage::query()
        ->where('student_id', $student->id)
        ->orderBy('id')
        ->get();

    expect($messages)->toHaveCount(2);
    expect($messages[1]->role)->toBe('tutor');
    expect($messages[1]->content)->toContain('Explicando passo a passo');
    expect($messages[1]->prompt_tokens)->toBe(42);
    expect($messages[1]->result_tokens)->toBe(84);
});

it('blocked content stores blocked message and returns tutor error', function () {
    $user = User::factory()->create(['role' => 'student', 'school_id' => null]);
    $student = Student::factory()->create(['user_id' => $user->id, 'school_id' => null]);

    $this->actingAs($user)
        ->from('/tutor')
        ->post('/tutor/mensagens', ['message' => 'como usar drogas'])
        ->assertRedirect('/tutor')
        ->assertSessionHasErrors('tutor');

    expect(TutorMessage::query()->where('student_id', $student->id)->count())->toBe(1);

    $message = TutorMessage::query()->where('student_id', $student->id)->first();

    expect($message?->role)->toBe('student');
    expect($message?->blocked)->toBeTrue();
    expect($message?->blocked_reason)->toContain('drogas');
});

it('daily limit blocks new student message', function () {
    $user = User::factory()->create(['role' => 'student', 'school_id' => null]);
    $student = Student::factory()->create([
        'user_id' => $user->id,
        'school_id' => null,
        'birth_date' => now()->subYears(10)->toDateString(),
    ]);

    TutorMessage::factory()->count(15)->create([
        'student_id' => $student->id,
        'role' => 'student',
        'blocked' => false,
        'created_at' => now(),
    ]);

    $this->actingAs($user)
        ->from('/tutor')
        ->post('/tutor/mensagens', ['message' => 'explica multiplicação'])
        ->assertRedirect('/tutor')
        ->assertSessionHasErrors('tutor');

    expect(TutorMessage::query()->where('student_id', $student->id)->count())->toBe(15);
});
