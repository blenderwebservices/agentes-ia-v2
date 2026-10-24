<?php

namespace Tests\Feature;

use App\Models\Agent;
use App\Models\Task;
use App\Models\User;
use App\Services\GeminiAgentService;
use App\Services\MultiAgentOrchestratorService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class MultiAgentSystemTest extends TestCase
{
    use RefreshDatabase;

    public function test_landing_page_renders_successfully(): void
    {
        Agent::create([
            'nombre' => 'Agente Demo',
            'modelo' => 'gemini-1.5-flash',
            'system_instruction' => 'Instrucción de prueba',
            'temperatura' => 0.7,
            'max_tokens' => 1024,
            'estado' => true,
        ]);

        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('MultiAgent');
        $response->assertSee('.Studio');
        $response->assertSee('Agente Demo');
    }

    public function test_gemini_service_reads_agent_configuration(): void
    {
        $agent = Agent::create([
            'nombre' => 'Revisor de Código',
            'modelo' => 'gemini-1.5-pro',
            'system_instruction' => 'Revisa estándares PSR-12 y seguridad en Laravel.',
            'temperatura' => 0.3,
            'max_tokens' => 1500,
            'estado' => true,
        ]);

        $service = app(GeminiAgentService::class);
        $result = $service->generate($agent, 'Analizar función de validación de contraseñas');

        $this->assertIsArray($result);
        $this->assertArrayHasKey('text', $result);
        $this->assertArrayHasKey('duration_ms', $result);
        $this->assertNotEmpty($result['text']);
    }

    public function test_orchestrator_runs_sequential_task_pipeline(): void
    {
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        $agent1 = Agent::create([
            'nombre' => 'Investigador',
            'modelo' => 'gemini-1.5-flash',
            'system_instruction' => 'Eres un analista.',
            'temperatura' => 0.5,
            'max_tokens' => 1000,
            'estado' => true,
        ]);

        $agent2 = Agent::create([
            'nombre' => 'Redactor',
            'modelo' => 'gemini-1.5-flash',
            'system_instruction' => 'Eres un redactor.',
            'temperatura' => 0.7,
            'max_tokens' => 1000,
            'estado' => true,
        ]);

        $task = Task::create([
            'user_id' => $admin->id,
            'titulo' => 'Investigación y Redacción',
            'modo_ejecucion' => 'secuencial',
            'prompt_plantilla' => 'Procesar: {input}',
            'estado' => true,
        ]);

        $task->agents()->attach([
            $agent1->id => ['orden' => 1, 'rol_en_tarea' => 'Fase 1: Análisis'],
            $agent2->id => ['orden' => 2, 'rol_en_tarea' => 'Fase 2: Redacción'],
        ]);

        $orchestrator = app(MultiAgentOrchestratorService::class);
        $run = $orchestrator->executeTask($task, 'Crear microservicio en PHP', $admin);

        $this->assertEquals('completado', $run->estado);
        $this->assertEquals('secuencial', $run->modo_ejecucion);
        $this->assertNotEmpty($run->resultado_final);
        $this->assertCount(2, $run->agentExecutions);
    }

    public function test_orchestrator_runs_parallel_task(): void
    {
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        $agent1 = Agent::create([
            'nombre' => 'Seguridad',
            'modelo' => 'gemini-1.5-flash',
            'system_instruction' => 'Evalúa seguridad.',
            'temperatura' => 0.2,
            'max_tokens' => 1000,
            'estado' => true,
        ]);

        $agent2 = Agent::create([
            'nombre' => 'Rendimiento',
            'modelo' => 'gemini-1.5-flash',
            'system_instruction' => 'Evalúa rendimiento.',
            'temperatura' => 0.2,
            'max_tokens' => 1000,
            'estado' => true,
        ]);

        $task = Task::create([
            'user_id' => $admin->id,
            'titulo' => 'Evaluación de Seguridad y Rendimiento',
            'modo_ejecucion' => 'paralelo',
            'estado' => true,
        ]);

        $task->agents()->attach([
            $agent1->id => ['orden' => 1],
            $agent2->id => ['orden' => 2],
        ]);

        $orchestrator = app(MultiAgentOrchestratorService::class);
        $run = $orchestrator->executeTask($task, 'Evaluación de arquitectura de colas', $admin);

        $this->assertEquals('completado', $run->estado);
        $this->assertEquals('paralelo', $run->modo_ejecucion);
        $this->assertStringContainsString('Resultado Consolidado', $run->resultado_final);
        $this->assertCount(2, $run->agentExecutions);
    }

    public function test_role_policies_restrict_standard_users(): void
    {
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        $user = User::create([
            'name' => 'Estándar',
            'email' => 'user@test.com',
            'password' => bcrypt('password'),
            'role' => 'user',
        ]);

        // Un admin puede crear agentes
        $this->assertTrue(Gate::forUser($admin)->allows('create', Agent::class));

        // Un usuario estándar NO puede crear agentes
        $this->assertFalse(Gate::forUser($user)->allows('create', Agent::class));

        // Un admin puede crear usuarios
        $this->assertTrue(Gate::forUser($admin)->allows('create', User::class));

        // Un usuario estándar NO puede crear usuarios
        $this->assertFalse(Gate::forUser($user)->allows('create', User::class));
    }
}
