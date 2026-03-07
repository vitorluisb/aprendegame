<?php

namespace App\Filament\Resources\GkSessions\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class GkSessionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('user.email')->label('Usuário')->disabled(),
            TextInput::make('score')->label('Pontuação')->disabled(),
            TextInput::make('current_round')->label('Rodada')->disabled(),
            TextInput::make('status')->label('Status')->disabled(),
        ]);
    }
}
