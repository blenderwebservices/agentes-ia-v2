<?php

namespace App\Filament\Widgets;

use App\Models\Agent;
use App\Models\Task;
use App\Models\TaskRun;
use App\Services\GeminiAgentService;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class GeminiApiStatusWidget extends BaseWidget
{
    protected static ?int $sort = -2;

    protected function getStats(): array
    {
        $geminiService = app(GeminiAgentService::class);
        $status = $geminiService->checkApiKeyStatus();

        $isReal = $status['mode'] === 'real';

        return [
            Stat::make('Motor de Inteligencia Artificial', $isReal ? 'Modo Real (En Vivo)' : 'Modo Mockup (Simulación)')
                ->description($status['message'])
                ->descriptionIcon($isReal ? 'heroicon-m-check-badge' : 'heroicon-m-exclamation-triangle')
                ->color($isReal ? 'success' : 'warning')
                ->chart($isReal ? [7, 5, 8, 10, 9, 12, 14] : [2, 2, 2, 2, 2]),

            Stat::make('Google Gemini API Key', $status['key_preview'])
                ->description($isReal ? 'HTTP Status: 200 OK | Autenticado' : 'Sin clave válida detectada en .env')
                ->descriptionIcon('heroicon-m-key')
                ->color($isReal ? 'success' : 'gray'),

            Stat::make('Agentes IA Activos', Agent::where('estado', true)->count() . ' Configurados')
                ->description('Modelos: 1.5 Flash, 1.5 Pro, 2.0 Flash')
                ->descriptionIcon('heroicon-m-cpu-chip')
                ->color('primary'),

            Stat::make('Ejecuciones Realizadas', (string) TaskRun::count())
                ->description(TaskRun::count() > 0 ? 'Última: ' . TaskRun::latest()->first()->created_at->diffForHumans() : 'Sin ejecuciones')
                ->descriptionIcon('heroicon-m-clock')
                ->color('info'),
        ];
    }
}
