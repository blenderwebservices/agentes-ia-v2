<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TaskRunResource\Pages;
use App\Models\TaskRun;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class TaskRunResource extends Resource
{
    protected static ?string $model = TaskRun::class;

    protected static ?string $navigationIcon = 'heroicon-o-clock';

    protected static ?string $navigationGroup = 'Orquestación de IA';

    protected static ?string $modelLabel = 'Ejecución';

    protected static ?string $pluralModelLabel = 'Historial de Ejecuciones';

    protected static ?int $navigationSort = 3;

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Resumen de la Ejecución')
                    ->schema([
                        Infolists\Components\TextEntry::make('task.titulo')
                            ->label('Tarea Multiagente')
                            ->weight('bold'),

                        Infolists\Components\TextEntry::make('modo_ejecucion')
                            ->label('Modo')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'secuencial' => 'info',
                                'paralelo' => 'purple',
                                default => 'gray',
                            }),

                        Infolists\Components\TextEntry::make('estado')
                            ->label('Estado')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'completado' => 'success',
                                'en_proceso' => 'warning',
                                'fallido' => 'danger',
                                default => 'gray',
                            }),

                        Infolists\Components\TextEntry::make('duracion_ms')
                            ->label('Duración')
                            ->formatStateUsing(fn ($state) => number_format($state) . ' ms'),

                        Infolists\Components\TextEntry::make('user.name')
                            ->label('Ejecutado por')
                            ->default('Sistema / Anónimo'),

                        Infolists\Components\TextEntry::make('modo_motor')
                            ->label('Motor de IA')
                            ->state(fn (TaskRun $record): string => str_contains($record->resultado_final ?? '', 'Modo Mockup') ? 'Modo Mockup (Simulación)' : 'Modo Real (Google Gemini)')
                            ->badge()
                            ->color(fn (string $state): string => str_contains($state, 'Mockup') ? 'warning' : 'success'),

                        Infolists\Components\TextEntry::make('iniciado_el')
                            ->label('Fecha y Hora')
                            ->dateTime('d/m/Y H:i:s'),
                    ])->columns(3),

                Infolists\Components\Section::make('Entrada Proporcionada por el Usuario')
                    ->schema([
                        Infolists\Components\TextEntry::make('input_usuario')
                            ->label('')
                            ->markdown()
                            ->columnSpanFull(),
                    ]),

                Infolists\Components\Section::make('Resultado Final de la Orquestación')
                    ->description('Output definitivo generado por la colaboración de los agentes de Gemini.')
                    ->schema([
                        Infolists\Components\TextEntry::make('resultado_final')
                            ->label('')
                            ->markdown()
                            ->copyable()
                            ->columnSpanFull(),
                    ]),

                Infolists\Components\Section::make('Desglose de Ejecución por Agente (Pipeline Audit)')
                    ->description('Detalle paso a paso del prompt inyectado, system instruction y respuesta de cada agente.')
                    ->schema([
                        Infolists\Components\RepeatableEntry::make('agentExecutions')
                            ->label('')
                            ->schema([
                                Infolists\Components\Grid::make(4)
                                    ->schema([
                                        Infolists\Components\TextEntry::make('paso')
                                            ->label('Paso #')
                                            ->badge()
                                            ->color('primary'),

                                        Infolists\Components\TextEntry::make('agent.nombre')
                                            ->label('Agente')
                                            ->weight('bold'),

                                        Infolists\Components\TextEntry::make('agent.modelo')
                                            ->label('Modelo')
                                            ->badge(),

                                        Infolists\Components\TextEntry::make('duracion_ms')
                                            ->label('Latencia')
                                            ->formatStateUsing(fn ($state) => number_format($state) . ' ms'),
                                    ]),

                                Infolists\Components\Tabs::make('Detalles del Agente')
                                    ->tabs([
                                        Infolists\Components\Tabs\Tab::make('Respuesta Generada')
                                            ->schema([
                                                Infolists\Components\TextEntry::make('output_agente')
                                                    ->label('')
                                                    ->markdown(),
                                            ]),
                                        Infolists\Components\Tabs\Tab::make('Prompt Enviado')
                                            ->schema([
                                                Infolists\Components\TextEntry::make('input_agente')
                                                    ->label('')
                                                    ->markdown(),
                                            ]),
                                        Infolists\Components\Tabs\Tab::make('System Instruction')
                                            ->schema([
                                                Infolists\Components\TextEntry::make('system_instruction_usado')
                                                    ->label('')
                                                    ->markdown(),
                                            ]),
                                    ]),
                            ])
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('#')
                    ->sortable(),

                Tables\Columns\TextColumn::make('task.titulo')
                    ->label('Tarea')
                    ->weight('bold')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('modo_ejecucion')
                    ->label('Modo')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'secuencial' => 'info',
                        'paralelo' => 'purple',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('modo_motor')
                    ->label('Motor')
                    ->state(fn (TaskRun $record): string => str_contains($record->resultado_final ?? '', 'Modo Mockup') ? 'Mockup' : 'En Vivo (Gemini)')
                    ->badge()
                    ->color(fn (string $state): string => $state === 'Mockup' ? 'warning' : 'success'),

                Tables\Columns\TextColumn::make('input_usuario')
                    ->label('Entrada')
                    ->limit(45)
                    ->searchable(),

                Tables\Columns\TextColumn::make('estado')
                    ->label('Estado')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'completado' => 'success',
                        'en_proceso' => 'warning',
                        'fallido' => 'danger',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('duracion_ms')
                    ->label('Duración')
                    ->formatStateUsing(fn ($state) => number_format($state) . ' ms')
                    ->sortable(),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Usuario')
                    ->default('Anónimo')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Fecha')
                    ->dateTime('d/m/Y H:i:s')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('estado')
                    ->options([
                        'completado' => 'Completado',
                        'en_proceso' => 'En Proceso',
                        'fallido' => 'Fallido',
                    ]),
                Tables\Filters\SelectFilter::make('modo_ejecucion')
                    ->options([
                        'secuencial' => 'Secuencial',
                        'paralelo' => 'Paralelo',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('Ver Detalle'),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTaskRuns::route('/'),
            'view' => Pages\ViewTaskRun::route('/{record}'),
        ];
    }
}
