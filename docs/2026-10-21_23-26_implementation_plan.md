# Plan de Implementación: Indicador de API Key Válida vs Modo Mockup (Frontend y Backend)

## 1. Objetivo
Implementar indicadores visuales y funcionales en tiempo real tanto en el **Frontend público** (Landing Page) como en el **Backend de administración** (FilamentPHP v3) que informen de forma clara si la plataforma está operando en **Modo Real** (con una API Key válida conectada a Google Gemini) o en **Modo Mockup** (simulación controlada).

---

## 2. Componentes a Desarrollar

### 2.1 Servicio Centralizado de Verificación (`GeminiAgentService.php`)
- Añadir método `checkApiKeyStatus(bool $forceRefresh = false): array`
- Verificar contra el endpoint `https://generativelanguage.googleapis.com/v1beta/models?key={$key}`
- Retornar estado estructurado: `mode` (`real` o `mockup`), `is_valid` (bool), `message`, `key_preview`, `status_code`
- Cachear el resultado (5 minutos) para evitar sobrecargar la cuota en cada renderizado.

### 2.2 Indicador en el Frontend Público (`welcome.blade.php`)
- **Barra de navegación:** Badge interactivo con punto pulsante:
  - 🟢 **Modo Real (Conectado):** verde con preview de la clave.
  - 🟡 **Modo Mockup (Simulado):** ámbar alertando sobre ejecución sin cuota o sin clave.
- **Hero & Simulador:** Notificación contextual de que las respuestas provienen de la API de Gemini en vivo o del motor de simulación.

### 2.3 Indicador en el Backend (`FilamentPHP v3`)
- **Render Hook Global en Topbar (`AdminPanelProvider`):** Badge persistente en la barra superior visible en todas las pantallas del panel (`panels::user-menu.before` / `panels::global-search.after`).
- **Widget en el Dashboard:** Widget con tarjeta informativa de salud de la conexión a Gemini, estado del modelo y botón de refresco / prueba de conexión.
- **Historial de Ejecuciones (`TaskRunResource`):** Badge indicativo en el infolist y tabla especificando si la ejecución fue real o mockup.

### 2.4 Pruebas y Validación
- Pruebas automatizadas actualizadas para validar la detección de estado de la API Key.
