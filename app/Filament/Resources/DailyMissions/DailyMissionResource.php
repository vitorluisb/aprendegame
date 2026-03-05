<?php

namespace App\Filament\Resources\DailyMissions;

use App\Filament\Resources\DailyMissions\Pages\CreateDailyMission;
use App\Filament\Resources\DailyMissions\Pages\EditDailyMission;
use App\Filament\Resources\DailyMissions\Pages\ListDailyMissions;
use App\Filament\Resources\DailyMissions\Schemas\DailyMissionForm;
use App\Filament\Resources\DailyMissions\Tables\DailyMissionsTable;
use App\Domain\Gameplay\Models\DailyMission;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class DailyMissionResource extends Resource
{
    protected static ?string $model = DailyMission::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedFire;

    protected static string|\UnitEnum|null $navigationGroup = 'Gamificação';

    protected static ?string $navigationLabel = 'Missões Diárias';

    protected static ?string $modelLabel = 'missão';

    protected static ?string $pluralModelLabel = 'missões';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return DailyMissionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DailyMissionsTable::configure($table);
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
            'index' => ListDailyMissions::route('/'),
            'create' => CreateDailyMission::route('/create'),
            'edit' => EditDailyMission::route('/{record}/edit'),
        ];
    }
}
