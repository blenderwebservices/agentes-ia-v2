<?php

namespace Database\Seeders;

use App\Models\Agent;
use App\Models\Task;
use App\Models\User;
use App\Services\MultiAgentOrchestratorService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Usuarios con Roles
        $admin = User::firstOrCreate(
            ['email' => 'admin@agentes.io'],
            [
                'name' => 'Administrador Principal',
                'password' => Hash::make('password'),
                'role' => 'admin',
            ]
        );

        $standardUser = User::firstOrCreate(
            ['email' => 'usuario@agentes.io'],
            [
                'name' => 'Usuario Estándar',
                'password' => Hash::make('password'),
                'role' => 'user',
            ]
        );

        // 2. Agentes de IA Especializados
        $redactor = Agent::firstOrCreate(
            ['nombre' => 'Redactor de Contenido Técnico'],
            [
                'descripcion' => 'Especialista en redactar explicaciones didácticas, guías técnicas y documentación de código con formato Markdown.',
                'modelo' => 'gemini-1.5-flash',
                'system_instruction' => 'Eres un redactor y divulgador técnico senior. Explica los conceptos de forma estructurada, con encabezados claros y bloques de código ejemplares.',
                'temperatura' => 0.70,
                'max_tokens' => 2048,
                'estado' => true,
                'avatar_color' => '#4f46e5',
            ]
        );

        $arquitecto = Agent::firstOrCreate(
            ['nombre' => 'Arquitecto de Software & Revisor'],
            [
                'descripcion' => 'Experto en arquitectura limpia, patrones de diseño (SOLID) y mejores prácticas para aplicaciones Laravel modernas.',
                'modelo' => 'gemini-1.5-pro',
                'system_instruction' => 'Eres un Arquitecto de Software Senior y Tech Lead. Revisa la lógica, estructura de clases, inyección de dependencias y asegura la máxima robustez en PHP.',
                'temperatura' => 0.30,
                'max_tokens' => 3000,
                'estado' => true,
                'avatar_color' => '#7c3aed',
            ]
        );

        $seguridad = Agent::firstOrCreate(
            ['nombre' => 'Auditor de Ciberseguridad'],
            [
                'descripcion' => 'Especialista en auditoría de seguridad para aplicaciones web (OWASP Top 10, sanitización, tokens y validaciones).',
                'modelo' => 'gemini-2.0-flash',
                'system_instruction' => 'Eres un Hacker Ético y Auditor de Seguridad. Analiza cada componente identificando posibles vectores de ataque (XSS, SQLi, CSRF, IDOR) y sugiere remediaciones inmediatas.',
                'temperatura' => 0.15,
                'max_tokens' => 2048,
                'estado' => true,
                'avatar_color' => '#059669',
            ]
        );

        $optimizador = Agent::firstOrCreate(
            ['nombre' => 'Especialista en Performance & SQL'],
            [
                'descripcion' => 'Experto en optimización de consultas en base de datos, caché con Redis y eficiencia de memoria.',
                'modelo' => 'gemini-1.5-flash',
                'system_instruction' => 'Eres un DBA y especialista en rendimiento de bases de datos. Analiza consultas Eloquent, índices y tiempos de respuesta.',
                'temperatura' => 0.25,
                'max_tokens' => 2048,
                'estado' => true,
                'avatar_color' => '#0284c7',
            ]
        );

        // 3. Tareas Multiagente
        // Tarea 1: Secuencial
        $tareaSecuencial = Task::firstOrCreate(
            ['titulo' => 'Diseño, Implementación y Auditoría de Endpoint Seguro'],
            [
                'user_id' => $admin->id,
                'descripcion' => 'Pipeline de 3 pasos: Arquitectura ➔ Redacción Técnica ➔ Auditoría de Seguridad.',
                'modo_ejecucion' => 'secuencial',
                'prompt_plantilla' => "Por favor procesa el siguiente requerimiento técnico:\n\n{input}\n\nAplica todos los estándares de excelencia del equipo.",
                'estado' => true,
            ]
        );

        $tareaSecuencial->agents()->syncWithoutDetaching([
            $arquitecto->id => ['orden' => 1, 'rol_en_tarea' => 'Diseñar arquitectura y estructura de clases'],
            $redactor->id => ['orden' => 2, 'rol_en_tarea' => 'Documentar y detallar la solución'],
            $seguridad->id => ['orden' => 3, 'rol_en_tarea' => 'Auditar vectores de seguridad y certificar código'],
        ]);

        // Tarea 2: Paralela
        $tareaParalela = Task::firstOrCreate(
            ['titulo' => 'Evaluación Multidisciplinaria de Arquitectura Cloud'],
            [
                'user_id' => $admin->id,
                'descripcion' => 'Análisis concurrente donde cada agente evalúa la propuesta simultáneamente desde su especialidad.',
                'modo_ejecucion' => 'paralelo',
                'prompt_plantilla' => "Evalúa la siguiente propuesta de arquitectura:\n\n{input}",
                'estado' => true,
            ]
        );

        $tareaParalela->agents()->syncWithoutDetaching([
            $arquitecto->id => ['orden' => 1, 'rol_en_tarea' => 'Evaluación de mantenibilidad y patrones'],
            $seguridad->id => ['orden' => 2, 'rol_en_tarea' => 'Evaluación de seguridad y superficie de ataque'],
            $optimizador->id => ['orden' => 3, 'rol_en_tarea' => 'Evaluación de rendimiento y escalabilidad de BD'],
        ]);

        // 4. Ejecución de demostración inicial para poblar el Historial
        try {
            $orchestrator = app(MultiAgentOrchestratorService::class);
            $orchestrator->executeTask(
                $tareaSecuencial,
                'Crear un sistema de autenticación de API mediante tokens de acceso personal con límite de tasa (rate limiting).',
                $admin
            );
        } catch (\Exception $e) {
            // Ignorar en caso de simulación inicial
        }
    }
}
