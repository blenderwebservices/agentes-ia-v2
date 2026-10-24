<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TaskResource\Pages;
use App\Filament\Resources\TaskResource\RelationManagers\AgentsRelationManager;
use App\Models\Task;
use App\Services\MultiAgentOrchestratorService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class TaskResource extends Resource
{
    protected static ?string $model = Task::class;

    protected static ?string $navigationIcon = 'heroicon-o-queue-list';

    protected static ?string $navigationGroup = 'Orquestación de IA';

    protected static ?string $modelLabel = 'Tarea Multiagente';

    protected static ?string $pluralModelLabel = 'Tareas Multiagente';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Configuración General de la Tarea')
                    ->description('Define el objetivo, modo de colaboración de los agentes y plantilla base.')
                    ->schema([
                        Forms\Components\TextInput::make('titulo')
                            ->label('Título de la Tarea')
                            ->placeholder('Ej. Redacción de Artículo con Revisión Editorial y SEO')
                            ->required()
                            ->maxLength(255)
                            ->columnSpan(2),

                        Forms\Components\Select::make('modo_ejecucion')
                            ->label('Modo de Orquestación')
                            ->options([
                                'secuencial' => 'Secuencial (En cadena: Salida del Agente 1 ➔ Entrada del Agente 2)',
                                'paralelo' => 'Paralelo (Concurrente: Todos los agentes analizan y consolidan)',
                            ])
                            ->default('secuencial')
                            ->required()
                            ->columnSpan(2),

                        Forms\Components\Toggle::make('estado')
                            ->label('Tarea Activa')
                            ->default(true)
                            ->required(),

                        Forms\Components\Textarea::make('descripcion')
                            ->label('Descripción / Propósito')
                            ->placeholder('Explica qué resuelve esta tarea multiagente...')
                            ->rows(2)
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('prompt_plantilla')
                            ->label('Plantilla de Prompt (Opcional)')
                            ->helperText('Usa el comodín {input} donde se inyectará el contenido proporcionado por el usuario.')
                            ->placeholder("Actúa sobre el siguiente texto proporcionado por el usuario:\n\n{input}\n\nAplica todos los estándares de calidad requeridos.")
                            ->rows(4)
                            ->columnSpanFull(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('titulo')
                    ->label('Título de la Tarea')
                    ->description(fn (Task $record): ?string => $record->descripcion)
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('modo_ejecucion')
                    ->label('Modo')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'secuencial' => 'info',
                        'paralelo' => 'purple',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => ucfirst($state)),

                Tables\Columns\TextColumn::make('agents_count')
                    ->label('Agentes')
                    ->counts('agents')
                    ->badge()
                    ->color('success'),

                Tables\Columns\IconColumn::make('estado')
                    ->label('Activo')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('modo_ejecucion')
                    ->options([
                        'secuencial' => 'Secuencial',
                        'paralelo' => 'Paralelo',
                    ]),
                Tables\Filters\TernaryFilter::make('estado'),
            ])
            ->actions([
                Tables\Actions\Action::make('ejecutar')
                    ->label('Ejecutar')
                    ->icon('heroicon-o-play')
                    ->color('success')
                    ->modalHeading(fn (Task $record) => "Ejecutar Tarea Multiagente: {$record->titulo}")
                    ->modalDescription('Introduce la entrada o solicitud para que los agentes asignados colaboren en tiempo real.')
                    ->modalSubmitActionLabel('Iniciar Orquestación')
                    ->form([
                        Forms\Components\Textarea::make('input_usuario')
                            ->label('Entrada / Requerimiento del Usuario')
                            ->placeholder('Describe detalladamente lo que necesitas que resuelvan los agentes...')
                            ->rows(5)
                            ->required(),
                    ])
                    ->action(function (Task $record, array $data) {
                        try {
                            $orchestrator = app(MultiAgentOrchestratorService::class);
                            $run = $orchestrator->executeTask($record, $data['input_usuario'], auth()->user());

                            Notification::make()
                                ->title('Tarea ejecutada exitosamente')
                                ->body("La ejecución #{$run->id} ha finalizado en {$run->duracion_ms} ms.")
                                ->success()
                                ->send();

                            return redirect(TaskRunResource::getUrl('view', ['record' => $run]));
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Error durante la orquestación')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
                Tables\Actions\EditAction::make(),
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
        return [
            AgentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTasks::route('/'),
            'create' => Pages\CreateTask::route('/create'),
            'edit' => Pages\EditTask::route('/{record}/edit'),
        ];
    }
}
