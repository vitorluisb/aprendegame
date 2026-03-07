<?php

namespace App\Filament\Resources\GkQuestions;

use App\Domain\QuizMestre\Models\GkQuestion;
use App\Filament\Resources\GkQuestions\Pages\CreateGkQuestion;
use App\Filament\Resources\GkQuestions\Pages\EditGkQuestion;
use App\Filament\Resources\GkQuestions\Pages\ListGkQuestions;
use App\Filament\Resources\GkQuestions\Schemas\GkQuestionForm;
use App\Filament\Resources\GkQuestions\Tables\GkQuestionsTable;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class GkQuestionResource extends Resource
{
    protected static ?string $model = GkQuestion::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedQuestionMarkCircle;

    protected static string|\UnitEnum|null $navigationGroup = 'Quiz Mestre';

    protected static ?string $navigationLabel = 'Questões';

    protected static ?string $modelLabel = 'questão';

    protected static ?string $pluralModelLabel = 'questões';

    public static function form(Schema $schema): Schema
    {
        return GkQuestionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return GkQuestionsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListGkQuestions::route('/'),
            'create' => CreateGkQuestion::route('/create'),
            'edit' => EditGkQuestion::route('/{record}/edit'),
        ];
    }
}
