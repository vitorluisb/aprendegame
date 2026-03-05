<?php

namespace App\Filament\Resources\BnccSkills;

use App\Filament\Resources\BnccSkills\Pages\CreateBnccSkill;
use App\Filament\Resources\BnccSkills\Pages\EditBnccSkill;
use App\Filament\Resources\BnccSkills\Pages\ListBnccSkills;
use App\Filament\Resources\BnccSkills\Schemas\BnccSkillForm;
use App\Filament\Resources\BnccSkills\Tables\BnccSkillsTable;
use App\Domain\Content\Models\BnccSkill;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class BnccSkillResource extends Resource
{
    protected static ?string $model = BnccSkill::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedLightBulb;

    protected static string|\UnitEnum|null $navigationGroup = 'Currículo BNCC';

    protected static ?string $navigationLabel = 'Habilidades BNCC';

    protected static ?string $modelLabel = 'habilidade';

    protected static ?string $pluralModelLabel = 'habilidades';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return BnccSkillForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BnccSkillsTable::configure($table);
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
            'index' => ListBnccSkills::route('/'),
            'create' => CreateBnccSkill::route('/create'),
            'edit' => EditBnccSkill::route('/{record}/edit'),
        ];
    }
}
