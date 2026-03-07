<?php

namespace App\Filament\Resources\GkSessions;

use App\Domain\QuizMestre\Models\GkSession;
use App\Filament\Resources\GkSessions\Pages\ListGkSessions;
use App\Filament\Resources\GkSessions\Schemas\GkSessionForm;
use App\Filament\Resources\GkSessions\Tables\GkSessionsTable;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class GkSessionResource extends Resource
{
    protected static ?string $model = GkSession::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChartBar;

    protected static string|\UnitEnum|null $navigationGroup = 'Quiz Mestre';

    protected static ?string $navigationLabel = 'Partidas';

    protected static ?string $modelLabel = 'partida';

    protected static ?string $pluralModelLabel = 'partidas';

    public static function form(Schema $schema): Schema
    {
        return GkSessionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return GkSessionsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListGkSessions::route('/'),
        ];
    }
}
