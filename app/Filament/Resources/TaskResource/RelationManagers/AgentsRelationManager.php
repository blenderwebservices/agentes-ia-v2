<?php

namespace App\Filament\Resources\TaskResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class AgentsRelationManager extends RelationManager
{
    protected static string $relationship = 'agents';

    protected static ?string $title = 'Agentes Asignados a la Tarea';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('orden')
                    ->label('Paso / Orden de Ejecución')
                    ->numeric()
                    ->default(1)
                    ->required(),

                Forms\Components\TextInput::make('rol_en_tarea')
                    ->label('Directiva / Rol en esta Tarea')
                    ->placeholder('Ej. Redactar el borrador inicial, Auditar seguridad, Traducir al inglés...')
                    ->maxLength(255)
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('nombre')
            ->defaultSort('orden')
            ->reorderable('orden')
            ->columns([
                Tables\Columns\TextColumn::make('orden')
                    ->label('Paso')
                    ->badge()
                    ->color('primary')
                    ->sortable(),

                Tables\Columns\TextColumn::make('nombre')
                    ->label('Agente')
                    ->description(fn ($record): ?string => $record->pivot->rol_en_tarea)
                    ->weight('bold')
                    ->searchable(),

                Tables\Columns\TextColumn::make('modelo')
                    ->label('Modelo')
                    ->badge()
                    ->color(fn (string $state): string => match (true) {
                        str_contains($state, 'pro') => 'warning',
                        str_contains($state, '2.0') => 'success',
                        default => 'info',
                    }),

                Tables\Columns\IconColumn::make('estado')
                    ->label('Activo')
                    ->boolean(),
            ])
            ->headerActions([
                Tables\Actions\AttachAction::make()
                    ->label('Asignar Agente')
                    ->preloadRecordSelect()
                    ->form(fn (Tables\Actions\AttachAction $action): array => [
                        $action->getRecordSelect(),
                        Forms\Components\TextInput::make('orden')
                            ->label('Paso en la secuencia')
                            ->numeric()
                            ->default(1)
                            ->required(),
                        Forms\Components\TextInput::make('rol_en_tarea')
                            ->label('Directiva / Enfoque para esta Tarea')
                            ->placeholder('Ej. Analizar requerimientos y generar estructura inicial')
                            ->columnSpanFull(),
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label('Editar Asignación'),
                Tables\Actions\DetachAction::make()
                    ->label('Quitar'),
            ]);
    }
}
