<?php

namespace App\Services;

use App\Models\Agent;
use App\Models\AgentExecution;
use App\Models\Task;
use App\Models\TaskRun;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Log;

class MultiAgentOrchestratorService
{
    public function __construct(
        protected GeminiAgentService $geminiService
    ) {}

    /**
     * Ejecuta una tarea multiagente (secuencial o en paralelo).
     *
     * @param Task $task Tarea configurada con agentes asociados
     * @param string $userInput Entrada o requerimiento del usuario
     * @param User|null $user Usuario que dispara la tarea
     * @return TaskRun Registro de la ejecución completada
     */
    public function executeTask(Task $task, string $userInput, ?User $user = null): TaskRun
    {
        $taskRun = TaskRun::create([
            'task_id' => $task->id,
            'user_id' => $user?->id ?? $task->user_id,
            'input_usuario' => $userInput,
            'modo_ejecucion' => $task->modo_ejecucion,
            'estado' => 'en_proceso',
            'iniciado_el' => now(),
        ]);

        $startTime = microtime(true);

        try {
            // Obtener agentes activos ordenados por el índice de orden
            $agents = $task->agents()->where('estado', true)->orderByPivot('orden')->get();

            if ($agents->isEmpty()) {
                throw new Exception("La tarea '{$task->titulo}' no tiene agentes activos asignados.");
            }

            if ($task->modo_ejecucion === 'secuencial') {
                $finalOutput = $this->runSequential($taskRun, $agents, $userInput, $task);
            } else {
                $finalOutput = $this->runParallel($taskRun, $agents, $userInput, $task);
            }

            $totalDurationMs = (int) round((microtime(true) - $startTime) * 1000);

            $taskRun->update([
                'resultado_final' => $finalOutput,
                'estado' => 'completado',
                'duracion_ms' => $totalDurationMs,
                'finalizado_el' => now(),
            ]);

            return $taskRun->fresh(['agentExecutions.agent']);

        } catch (Exception $e) {
            $totalDurationMs = (int) round((microtime(true) - $startTime) * 1000);

            Log::error('Error en orquestación multiagente', [
                'task_run_id' => $taskRun->id,
                'error' => $e->getMessage(),
            ]);

            $taskRun->update([
                'estado' => 'fallido',
                'error' => $e->getMessage(),
                'duracion_ms' => $totalDurationMs,
                'finalizado_el' => now(),
            ]);

            throw $e;
        }
    }

    /**
     * Orquestación Secuencial: El output de cada agente alimenta el input del siguiente.
     */
    protected function runSequential(TaskRun $taskRun, $agents, string $userInput, Task $task): string
    {
        $currentContext = $userInput;
        $step = 1;
        $totalAgents = $agents->count();

        foreach ($agents as $agent) {
            // Construir el prompt para este paso
            if ($step === 1) {
                $promptForAgent = $this->buildInitialPrompt($task, $userInput, $agent);
            } else {
                $promptForAgent = "### Contexto Original del Usuario:\n{$userInput}\n\n" .
                    "### Entrada del Agente Previo:\n{$currentContext}\n\n" .
                    "### Instrucción para este Paso:\n" .
                    ($agent->pivot->rol_en_tarea ?: "Procesa y refina la información anterior aportando tu especialidad.");
            }

            $execution = AgentExecution::create([
                'task_run_id' => $taskRun->id,
                'agent_id' => $agent->id,
                'paso' => $step,
                'input_agente' => $promptForAgent,
                'system_instruction_usado' => $agent->system_instruction,
                'estado' => 'completado',
            ]);

            try {
                $result = $this->geminiService->generate($agent, $promptForAgent);

                $execution->update([
                    'output_agente' => $result['text'],
                    'duracion_ms' => $result['duration_ms'],
                    'tokens_utilizados' => $result['tokens_used'],
                    'estado' => 'completado',
                ]);

                // Actualizar contexto para el siguiente agente
                $currentContext = $result['text'];
            } catch (Exception $e) {
                $execution->update([
                    'estado' => 'fallido',
                    'error' => $e->getMessage(),
                ]);
                throw $e;
            }

            $step++;
        }

        return $currentContext;
    }

    /**
     * Orquestación Paralela: Todos los agentes analizan la entrada simultáneamente y se sintetiza.
     */
    protected function runParallel(TaskRun $taskRun, $agents, string $userInput, Task $task): string
    {
        $step = 1;
        $parallelOutputs = [];

        foreach ($agents as $agent) {
            $promptForAgent = $this->buildInitialPrompt($task, $userInput, $agent);

            $execution = AgentExecution::create([
                'task_run_id' => $taskRun->id,
                'agent_id' => $agent->id,
                'paso' => $step,
                'input_agente' => $promptForAgent,
                'system_instruction_usado' => $agent->system_instruction,
            ]);

            try {
                $result = $this->geminiService->generate($agent, $promptForAgent);

                $execution->update([
                    'output_agente' => $result['text'],
                    'duracion_ms' => $result['duration_ms'],
                    'tokens_utilizados' => $result['tokens_used'],
                    'estado' => 'completado',
                ]);

                $parallelOutputs[$agent->nombre] = $result['text'];
            } catch (Exception $e) {
                $execution->update([
                    'estado' => 'fallido',
                    'error' => $e->getMessage(),
                ]);
                throw $e;
            }

            $step++;
        }

        // Sintetizar y consolidar las salidas paralelas
        $consolidated = "## 📊 Resultado Consolidado de la Ejecución Multiagente (Paralela)\n\n";
        $consolidated .= "**Entrada del Usuario:**\n> " . nl2br(e($userInput)) . "\n\n---\n\n";

        foreach ($parallelOutputs as $agentName => $output) {
            $consolidated .= "### 🤖 Aporte de: {$agentName}\n\n{$output}\n\n---\n\n";
        }

        return $consolidated;
    }

    protected function buildInitialPrompt(Task $task, string $userInput, Agent $agent): string
    {
        if (!empty($task->prompt_plantilla)) {
            $template = str_replace('{input}', $userInput, $task->prompt_plantilla);
            return $template . "\n\nRol específico asignado: " . ($agent->pivot->rol_en_tarea ?: 'Resolución de tarea');
        }

        return "Requerimiento a resolver:\n" . $userInput .
            ($agent->pivot->rol_en_tarea ? "\n\nTu enfoque en esta tarea: " . $agent->pivot->rol_en_tarea : '');
    }
}
