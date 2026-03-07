<?php

namespace App\Http\Controllers\Enem;

use App\Domain\Enem\Models\EnemQuestion;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class EnemPracticeController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Enem/Index', [
            'areas' => [
                ['key' => 'linguagens', 'label' => 'Linguagens, Códigos e suas Tecnologias'],
                ['key' => 'humanas', 'label' => 'Ciências Humanas e suas Tecnologias'],
                ['key' => 'natureza', 'label' => 'Ciências da Natureza e suas Tecnologias'],
                ['key' => 'matematica', 'label' => 'Matemática e suas Tecnologias'],
            ],
        ]);
    }

    public function start(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'area' => ['required', 'string', 'in:linguagens,humanas,natureza,matematica'],
            'difficulty' => ['nullable', 'string', 'in:easy,medium,hard'],
        ]);

        $query = EnemQuestion::query()
            ->where('is_active', true)
            ->whereIn('status', ['reviewed', 'approved'])
            ->where('area', (string) $data['area']);

        if (isset($data['difficulty'])) {
            $query->where('difficulty', (string) $data['difficulty']);
        }

        $question = $query->inRandomOrder()->first();

        if (! $question) {
            return redirect()->route('enem.index')->withErrors([
                'enem' => 'Não há questões ENEM aprovadas para esse filtro.',
            ]);
        }

        return redirect()->route('enem.play', ['question' => $question->id]);
    }

    public function play(EnemQuestion $question): Response|RedirectResponse
    {
        if (! $question->is_active || ! in_array($question->status, ['reviewed', 'approved'], true)) {
            return redirect()->route('enem.index');
        }

        return Inertia::render('Enem/Play', [
            'question' => [
                'id' => $question->id,
                'enem_code' => $question->enem_code,
                'area' => $question->area,
                'subject' => $question->subject,
                'difficulty' => $question->difficulty,
                'year_reference' => $question->year_reference,
                'context_text' => $question->context_text,
                'question_text' => $question->question_text,
                'options' => collect($question->optionsMap())
                    ->map(fn (string $text, string $key): array => ['key' => $key, 'text' => $text])
                    ->values()
                    ->all(),
                'explanation' => $question->explanation,
            ],
        ]);
    }

    public function answer(Request $request, EnemQuestion $question): JsonResponse
    {
        if (! $question->is_active || ! in_array($question->status, ['reviewed', 'approved'], true)) {
            return response()->json(['message' => 'Questão indisponível.'], 422);
        }

        $payload = $request->validate([
            'selected_option' => ['required', 'string', 'in:A,B,C,D,E'],
        ]);

        $selected = (string) $payload['selected_option'];
        $isCorrect = $selected === $question->correct_option;

        return response()->json([
            'correct' => $isCorrect,
            'selected_option' => $selected,
            'correct_option' => $question->correct_option,
            'explanation' => $question->explanation,
        ]);
    }
}
