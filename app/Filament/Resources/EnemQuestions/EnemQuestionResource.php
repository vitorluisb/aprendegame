<?php

namespace App\Filament\Resources\EnemQuestions;

use App\Domain\Enem\Models\EnemQuestion;
use App\Filament\Resources\EnemQuestions\Pages\CreateEnemQuestion;
use App\Filament\Resources\EnemQuestions\Pages\EditEnemQuestion;
use App\Filament\Resources\EnemQuestions\Pages\ListEnemQuestions;
use App\Filament\Resources\EnemQuestions\Schemas\EnemQuestionForm;
use App\Filament\Resources\EnemQuestions\Tables\EnemQuestionsTable;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class EnemQuestionResource extends Resource
{
    protected static ?string $model = EnemQuestion::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedAcademicCap;

    protected static string|\UnitEnum|null $navigationGroup = 'ENEM';

    protected static ?string $navigationLabel = 'Questões ENEM';

    protected static ?string $modelLabel = 'questão ENEM';

    protected static ?string $pluralModelLabel = 'questões ENEM';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return EnemQuestionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return EnemQuestionsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListEnemQuestions::route('/'),
            'create' => CreateEnemQuestion::route('/create'),
            'edit' => EditEnemQuestion::route('/{record}/edit'),
        ];
    }
}
