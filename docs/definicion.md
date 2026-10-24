Aquí tienes el prompt estructurado para que lo introduzcas en tu modelo de lenguaje de preferencia y genere la aplicación:

---

**Actúa como un Arquitecto de Software y Desarrollador Senior especializado en Laravel 11 y FilamentPHP v3.**

Tu objetivo es diseñar y generar la estructura, modelos, migraciones y controladores para una plataforma web multiagente impulsada por la API de Google Gemini.

**Restricción Arquitectónica Estricta:** Todo el sistema, incluida la orquestación de los agentes y la comunicación con la API de Gemini, debe desarrollarse nativamente en PHP/Laravel. Queda estrictamente prohibido el uso de Python, scripts externos o microservicios en otros lenguajes. Utiliza el `Illuminate\Support\Facades\Http` de Laravel o un SDK nativo de PHP para interactuar con Gemini.

Desarrolla la aplicación cubriendo los siguientes tres pilares:

### 1. Frontend Público (Objetivo y Presentación)

Crea una vista pública (Landing Page) utilizando Blade y TailwindCSS que:

* Explique claramente el objetivo de la plataforma y cómo los diferentes agentes de IA colaboran para resolver problemas.
* Tenga un diseño moderno y minimalista.
* Incluya llamadas a la acción (CTAs) para que los usuarios se registren o inicien sesión.

### 2. Backend de Administración (FilamentPHP)

Construye el panel de control utilizando FilamentPHP. Debe incluir los siguientes Recursos (Resources):

* **Gestión de Agentes (AgentResource):** Un CRUD para configurar los distintos agentes de Gemini. Los campos deben incluir:
* `nombre` (ej. Redactor, Revisor de Código).
* `descripcion` (propósito del agente).
* `modelo` (select con opciones como `gemini-1.5-flash`, `gemini-1.5-pro`).
* `system_instruction` (el prompt base que define el comportamiento del agente).
* `temperatura` y `max_tokens`.
* `estado` (activo/inactivo).


* **Gestión de Prompts/Tareas (TaskResource):** Interfaz donde se define una tarea y se asigna qué agentes participarán en su resolución (secuencial o en paralelo).
* **Historial de Ejecuciones:** Un panel en Filament de solo lectura para ver los inputs del usuario, los outputs de cada agente y el resultado final.

### 3. Control de Usuarios y Roles

Implementa un sistema de autenticación y autorización nativo integrado con Filament:

* **UserResource:** Para administrar el acceso a la plataforma.
* Implementa roles simples (Administrador y Usuario Estándar). Los administradores pueden crear/editar agentes; los usuarios estándar solo pueden ejecutar tareas y ver el frontend.

**Entregables esperados:**

1. Las migraciones necesarias para las tablas `agents`, `tasks` y la extensión de `users`.
2. Los Modelos Eloquent con sus relaciones.
3. El código completo de los archivos de Filament (Resources, Pages, Forms, y Tables) para la gestión de Agentes.
4. Una clase de servicio en Laravel (`GeminiAgentService.php`) que demuestre cómo leer la configuración del agente desde la base de datos y hacer la petición HTTP a la API de Gemini.

---
