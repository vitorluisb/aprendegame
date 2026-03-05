<?php

namespace App\Filament\Resources\AiJobs;

use App\Filament\Resources\AiJobs\Pages\CreateAiJob;
use App\Filament\Resources\AiJobs\Pages\EditAiJob;
use App\Filament\Resources\AiJobs\Pages\ListAiJobs;
use App\Filament\Resources\AiJobs\Schemas\AiJobForm;
use App\Filament\Resources\AiJobs\Tables\AiJobsTable;
use App\Domain\AI\Models\AiJob;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class AiJobResource extends Resource
{
    protected static ?string $model = AiJob::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSparkles;

    protected static string|\UnitEnum|null $navigationGroup = 'Inteligência Artificial';

    protected static ?string $navigationLabel = 'Geração por IA';

    protected static ?string $modelLabel = 'tarefa de IA';

    protected static ?string $pluralModelLabel = 'tarefas de IA';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return AiJobForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AiJobsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAiJobs::route('/'),
            'create' => CreateAiJob::route('/create'),
            'edit' => EditAiJob::route('/{record}/edit'),
        ];
    }
}
