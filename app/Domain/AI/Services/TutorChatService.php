<?php

namespace App\Domain\AI\Services;

use App\Domain\Accounts\Models\Student;
use App\Domain\AI\Models\TutorMessage;
use Illuminate\Support\Facades\Http;
use Throwable;

class TutorChatService
{
    private const TUTOR_MODEL = 'deepseek/deepseek-v3.2';

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

        [$reply, $promptTokens, $resultTokens] = $this->buildTutorReply($message);

        return TutorMessage::query()->create([
            'student_id' => $student->id,
            'role' => 'tutor',
            'content' => $reply,
            'blocked' => false,
            'blocked_reason' => null,
            'prompt_tokens' => $promptTokens,
            'result_tokens' => $resultTokens,
        ]);
    }

    /**
     * @return array{0:string,1:int,2:int}
     */
    private function buildTutorReply(string $message): array
    {
        $normalizedMessage = trim($message);
        $fallbackReply = "Vamos por partes:\n\n{$normalizedMessage}\n\nTente resolver em 3 passos: identificar o tema, lembrar a regra principal e testar com um exemplo simples.";

        $url = (string) config('services.ai.url', '');
        $key = (string) config('services.ai.key', '');

        if ($url === '' || $key === '') {
            return [$fallbackReply, 0, 0];
        }

        $prompt = <<<PROMPT
        Você é um tutor pedagógico para alunos brasileiros.
        Responda em português, de forma curta, clara e didática.
        Regras de resposta:
        1) Máximo de 4 linhas curtas.
        2) Linguagem simples.
        3) Vá direto ao ponto.
        4) No máximo 1 exemplo curto.
        5) Evite introduções longas e texto desnecessário.
        Mensagem do aluno: {$normalizedMessage}
        PROMPT;

        try {
            $client = Http::connectTimeout(10)
                ->timeout(12);

            if ($this->isOpenRouter($url)) {
                $response = $client
                    ->withHeaders([
                        'Authorization' => "Bearer {$key}",
                        'Content-Type' => 'application/json',
                    ])
                    ->post($url, [
                        'model' => self::TUTOR_MODEL,
                        'max_tokens' => 220,
                        'messages' => [
                            ['role' => 'user', 'content' => $prompt],
                        ],
                    ]);

                if (! $response->successful()) {
                    return [$fallbackReply, 0, 0];
                }

                $body = $response->json();
                $reply = $this->extractOpenRouterContent($body);

                if ($reply === '') {
                    return [$fallbackReply, 0, 0];
                }

                return [
                    $reply,
                    (int) ($body['usage']['prompt_tokens'] ?? 0),
                    (int) ($body['usage']['completion_tokens'] ?? 0),
                ];
            }

            $response = $client
                ->withHeaders([
                    'x-api-key' => $key,
                    'anthropic-version' => '2023-06-01',
                    'content-type' => 'application/json',
                ])
                ->post($url, [
                    'model' => 'claude-sonnet-4-6',
                    'max_tokens' => 220,
                    'messages' => [
                        ['role' => 'user', 'content' => $prompt],
                    ],
                ]);

            if (! $response->successful()) {
                return [$fallbackReply, 0, 0];
            }

            $body = $response->json();
            $reply = trim((string) ($body['content'][0]['text'] ?? ''));

            if ($reply === '') {
                return [$fallbackReply, 0, 0];
            }

            return [
                $reply,
                (int) ($body['usage']['input_tokens'] ?? 0),
                (int) ($body['usage']['output_tokens'] ?? 0),
            ];
        } catch (Throwable) {
            return [$fallbackReply, 0, 0];
        }
    }

    private function isOpenRouter(string $url): bool
    {
        return str_contains(strtolower($url), 'openrouter.ai');
    }

    /**
     * @param  array<string, mixed>  $body
     */
    private function extractOpenRouterContent(array $body): string
    {
        $content = $body['choices'][0]['message']['content'] ?? '';

        if (is_string($content)) {
            return trim($content);
        }

        if (! is_array($content)) {
            return '';
        }

        $parts = collect($content)
            ->map(fn ($part) => is_array($part) ? ($part['text'] ?? '') : '')
            ->filter(fn ($part) => is_string($part) && $part !== '')
            ->values()
            ->all();

        return trim(implode("\n", $parts));
    }
}
