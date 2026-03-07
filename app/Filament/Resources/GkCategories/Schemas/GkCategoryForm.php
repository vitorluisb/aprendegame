<?php

namespace App\Filament\Resources\GkCategories\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class GkCategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')->label('Nome')->required()->maxLength(255),
            Textarea::make('description')->label('Descrição')->rows(3),
            TextInput::make('icon')->label('Ícone')->placeholder('book-open')->maxLength(255),
        ]);
    }
}
