<?php

namespace App\Filament\Resources\Students\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class StudentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Dados do Aluno')
                    ->columns(2)
                    ->schema([
                        Select::make('user_id')
                            ->label('Conta de Usuário')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload()
                            ->helperText('Opcional: pode ser aluno sem login próprio.')
                            ->columnSpanFull(),

                        Select::make('school_id')
                            ->label('Escola')
                            ->relationship('school', 'name')
                            ->searchable()
                            ->preload(),

                        Select::make('grade_id')
                            ->label('Série')
                            ->relationship('grade', 'name')
                            ->searchable()
                            ->preload(),

                        TextInput::make('name')
                            ->label('Nome')
                            ->required()
                            ->maxLength(255),

                        DatePicker::make('birth_date')
                            ->label('Data de Nascimento')
                            ->native(false)
                            ->maxDate(now()),

                        TextInput::make('avatar_url')
                            ->label('URL do Avatar')
                            ->url()
                            ->maxLength(255)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
