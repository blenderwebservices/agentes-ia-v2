# Plan de Implementación: Plataforma Multiagente IA (Laravel 11 + FilamentPHP v3 + Google Gemini)

## Resumen Ejecutivo
Implementar una plataforma web multiagente completamente nativa en PHP/Laravel 11 con panel de control en FilamentPHP v3, frontend público en Blade + TailwindCSS, y orquestación de agentes conectada a la API de Google Gemini mediante `Illuminate\Support\Facades\Http` sin dependencias externas en Python u otros lenguajes.

---

## 1. Objetivos y Especificaciones (con base en `docs/definicion.md`)

1. **Frontend Público**:
   - Landing Page moderna, minimalista y responsiva en Blade y TailwindCSS.
   - Explicación del funcionamiento colaborativo de los agentes de IA.
   - Demostración visual y CTAs para login y registro / panel.

2. **Backend de Administración (FilamentPHP v3)**:
   - `AgentResource`: CRUD completo de agentes (nombre, descripción, modelo, system_instruction, temperatura, max_tokens, estado activo/inactivo).
   - `TaskResource`: Creación y configuración de tareas multiagente, orden de agentes y modo de ejecución (secuencial o paralelo), con botón de ejecución interactiva.
   - `TaskRunResource` (Historial de Ejecuciones): Panel Filament de solo lectura que detalla inputs, outputs por agente y resultado consolidado final.

3. **Control de Usuarios y Roles**:
   - `UserResource` para gestión de usuarios.
   - Roles: `admin` (administrador total) y `user` (usuario estándar que puede ejecutar tareas y ver frontend).
   - Políticas de autorización (`AgentPolicy`, `TaskPolicy`, `UserPolicy`).

4. **Servicio Nativo de Gemini & Orquestación**:
   - `GeminiAgentService`: Cliente HTTP nativo para la API REST de Google Gemini (`v1beta`).
   - `MultiAgentOrchestratorService`: Orquestación secuencial (pipeline encadenado) y paralela (con `Http::pool` concurrente).
   - Modo de prueba/fallback si no hay `GEMINI_API_KEY` configurada para que la suite y la app funcionen inmediatamente.

---

## 2. Fases de Ejecución

### Fase 1: Creación del Proyecto Laravel 11 y Configuración Base
- Crear proyecto Laravel 11 e instalar dependencias.
- Configurar base de datos SQLite y variables de entorno (`.env`).
- Instalar FilamentPHP v3 (`filament/filament`).

### Fase 2: Modelos, Migraciones y Base de Datos
- Migración de `users` (añadir campo `role` enum: `admin`, `user`).
- Migración de `agents` (`name`, `description`, `model`, `system_instruction`, `temperature`, `max_tokens`, `is_active`, `avatar_color`).
- Migración de `tasks` (`title`, `description`, `execution_mode`, `prompt_template`, `is_active`, `user_id`).
- Migración de tabla pivote `agent_task` (`task_id`, `agent_id`, `order_index`).
- Migración de `task_runs` (`task_id`, `user_id`, `input_text`, `final_output`, `status`, `execution_mode`, `started_at`, `completed_at`).
- Migración de `agent_executions` (`task_run_id`, `agent_id`, `step_number`, `prompt_sent`, `response_received`, `status`, `duration_ms`, `tokens_used`).
- Definición de Modelos Eloquent con relaciones `hasMany`, `belongsTo`, `belongsToMany`.

### Fase 3: Servicios Nativos de Gemini y Orquestador
- `app/Services/GeminiAgentService.php`:
  - Petición POST a `https://generativelanguage.googleapis.com/v1beta/models/{model}:generateContent`.
  - Configuración de temperatura, maxTokens y system_instruction.
  - Parsing de respuesta o captura de errores.
- `app/Services/MultiAgentOrchestratorService.php`:
  - Orquestación secuencial (canalización de outputs).
  - Orquestación paralela (concurrencia nativa en PHP con `Http::pool`).
  - Registro de auditoría y métricas por ejecución.

### Fase 4: Panel de Control FilamentPHP v3
- `AgentResource`: Formulario con sliders de temperatura, selector de modelo, editor de prompt, tablas con filtros y acciones.
- `TaskResource`: Repeater o relación de agentes asignados, selector de modo secuencial/paralelo, acción modal "Ejecutar Tarea" con input en vivo y ejecución directa.
- `TaskRunResource`: Vistas detalladas con Infolists y Timeline de ejecución de cada agente participante.
- `UserResource`: Gestión de usuarios y roles.
- `Filament/AdminPanelProvider`: Configuración de navegación, marcas y seguridad.

### Fase 5: Políticas de Seguridad y Roles
- `AgentPolicy`, `TaskPolicy`, `UserPolicy`.
- Acceso al panel y visibilidad condicional según rol (`admin` vs `user`).

### Fase 6: Frontend Público (Landing Page Blade + TailwindCSS)
- Vista pública `resources/views/welcome.blade.php`:
  - Hero interactivo con animaciones modernas.
  - Sección de agentes disponibles en la plataforma.
  - Simulador / demostración visual de orquestación en tiempo real.
  - Enlaces directos a inicio de sesión y registro.

### Fase 7: Seeders, Pruebas y Validación
- `DatabaseSeeder` con usuario administrador (`admin@agentes.io`), usuario estándar (`usuario@agentes.io`), agentes preconfigurados y tareas de ejemplo.
- Ejecución de migraciones y seeds.
- Verificación de ejecución de tareas y visualización en Filament.
- Reporte final de entrega (`walkthrough`).
