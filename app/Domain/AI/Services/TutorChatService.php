<?php

namespace App\Domain\AI\Services;

use App\Domain\Accounts\Models\Student;
use App\Domain\AI\Models\TutorMessage;

class TutorChatService
{
    public function answer(Student $student, string $message): TutorMessage
    {
        TutorMessage::query()->create([
            'student_id' => $student->id,
            'role' => 'student',
            'content' => $message,
            'blocked' => false,
            'blocked_reason' => null,
            'prompt_tokens' => 0,
            'result_tokens' => 0,
        ]);

        $normalized = trim($message);
        $reply = "Vamos por partes:\n\n".$normalized."\n\nTente resolver em 3 passos: identificar o tema, lembrar a regra principal e testar com um exemplo simples.";

        return TutorMessage::query()->create([
            'student_id' => $student->id,
            'role' => 'tutor',
            'content' => $reply,
            'blocked' => false,
            'blocked_reason' => null,
            'prompt_tokens' => 30,
            'result_tokens' => 60,
        ]);
    }
}
