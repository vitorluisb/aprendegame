<?php

namespace App\Filament\Resources\GkSessions\Pages;

use App\Domain\QuizMestre\Models\GkSession;
use App\Filament\Resources\GkSessions\GkSessionResource;
use Filament\Resources\Pages\ListRecords;

class ListGkSessions extends ListRecords
{
    protected static string $resource = GkSessionResource::class;

    public function getHeading(): string
    {
        $total = GkSession::query()->count();
        $completed = GkSession::query()->where('status', 'completed')->count();
        $avgScore = (int) round((float) GkSession::query()->avg('score'));

        return "Partidas: {$total} • Completas: {$completed} • Média de Pontos: {$avgScore}";
    }
}
