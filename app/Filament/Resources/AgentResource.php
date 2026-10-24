<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AgentResource\Pages;
use App\Models\Agent;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class AgentResource extends Resource
{
    protected static ?string $model = Agent::class;

    protected static ?string $navigationIcon = 'heroicon-o-cpu-chip';

    protected static ?string $navigationGroup = 'Orquestación de IA';

    protected static ?string $modelLabel = 'Agente';

    protected static ?string $pluralModelLabel = 'Agentes';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Identidad del Agente')
                    ->description('Configura el nombre y rol distintivo del agente en la plataforma.')
                    ->schema([
                        Forms\Components\TextInput::make('nombre')
                            ->label('Nombre del Agente')
                            ->placeholder('Ej. Redactor Técnico, Revisor de Código, Diseñador de Prompts')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\Select::make('modelo')
                            ->label('Modelo de Gemini')
                            ->options([
                                'gemini-2.5-flash' => 'Gemini 2.5 Flash (Recomendado, Rápido y Versátil)',
                                'gemini-2.5-pro' => 'Gemini 2.5 Pro (Razonamiento Profundo y Contexto Amplio)',
                                'gemini-flash-latest' => 'Gemini Flash Latest (Última versión estable)',
                                'gemini-pro-latest' => 'Gemini Pro Latest (Máximo Razonamiento)',
                                'gemini-3.5-flash' => 'Gemini 3.5 Flash (Generación Avanzada)',
                                'gemini-1.5-flash' => 'Gemini 1.5 Flash (Legacy / Compatible)',
                                'gemini-1.5-pro' => 'Gemini 1.5 Pro (Legacy / Compatible)',
                            ])
                            ->default('gemini-2.5-flash')
                            ->required(),

                        Forms\Components\ColorPicker::make('avatar_color')
                            ->label('Color Distintivo')
                            ->default('#4f46e5'),

                        Forms\Components\Toggle::make('estado')
                            ->label('Agente Activo')
                            ->helperText('Solo los agentes activos pueden ser seleccionados en tareas.')
                            ->default(true)
                            ->required(),

                        Forms\Components\Textarea::make('descripcion')
                            ->label('Propósito y Especialidad')
                            ->placeholder('Describe brevemente en qué se especializa este agente...')
                            ->rows(2)
                            ->columnSpanFull(),
                    ])->columns(2),

                Forms\Components\Section::make('Instrucciones del Sistema (System Prompt)')
                    ->description('Define la personalidad, directivas obligatorias y restricciones de comportamiento.')
                    ->schema([
                        Forms\Components\Textarea::make('system_instruction')
                            ->label('System Instruction')
                            ->helperText('Este texto se inyecta en la cabecera system_instruction de Gemini en cada llamada.')
                            ->placeholder("Eres un experto senior en desarrollo backend con Laravel. Tu responsabilidad es revisar exhaustivamente el código para encontrar vulnerabilidades y optimizar rendimiento...")
                            ->rows(6)
                            ->required()
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('Parámetros de Inferencia')
                    ->description('Ajusta la creatividad y longitud máxima de las respuestas de Gemini.')
                    ->schema([
                        Forms\Components\TextInput::make('temperatura')
                            ->label('Temperatura')
                            ->helperText('0.0 = Determinista / Analítico | 1.0 - 2.0 = Creativo / Exploratorio')
                            ->numeric()
                            ->minValue(0.0)
                            ->maxValue(2.0)
                            ->step(0.05)
                            ->default(0.70)
                            ->required(),

                        Forms\Components\TextInput::make('max_tokens')
                            ->label('Tokens Máximos de Salida')
                            ->helperText('Límite de tokens de la respuesta generada.')
                            ->numeric()
                            ->minValue(64)
                            ->maxValue(8192)
                            ->default(2048)
                            ->required(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ColorColumn::make('avatar_color')
                    ->label('')
                    ->width(40),

                Tables\Columns\TextColumn::make('nombre')
                    ->label('Agente')
                    ->description(fn (Agent $record): ?string => $record->descripcion)
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('modelo')
                    ->label('Modelo')
                    ->badge()
                    ->color(fn (string $state): string => match (true) {
                        str_contains($state, 'pro') => 'warning',
                        str_contains($state, '2.0') => 'success',
                        default => 'info',
                    })
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('temperatura')
                    ->label('Temp.')
                    ->numeric(decimalPlaces: 2)
                    ->sortable(),

                Tables\Columns\TextColumn::make('max_tokens')
                    ->label('Tokens')
                    ->numeric()
                    ->sortable(),

                Tables\Columns\IconColumn::make('estado')
                    ->label('Estado')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('modelo')
                    ->options([
                        'gemini-1.5-flash' => 'Gemini 1.5 Flash',
                        'gemini-1.5-pro' => 'Gemini 1.5 Pro',
                        'gemini-2.0-flash' => 'Gemini 2.0 Flash',
                    ]),
                Tables\Filters\TernaryFilter::make('estado')
                    ->label('Estado Activo'),
            ])
            ->actions([
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
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAgents::route('/'),
            'create' => Pages\CreateAgent::route('/create'),
            'edit' => Pages\EditAgent::route('/{record}/edit'),
        ];
    }
}
