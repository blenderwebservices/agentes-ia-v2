<?php

namespace App\Services;

use App\Models\Agent;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class GeminiAgentService
{
    protected ?string $apiKey;
    protected string $baseUrl;

    public function __construct()
    {
        $this->apiKey = config('services.gemini.api_key', env('GEMINI_API_KEY'));
        $this->baseUrl = config('services.gemini.base_url', 'https://generativelanguage.googleapis.com/v1beta');
    }

    /**
     * Verifica la validez de la API Key contra Google Gemini y retorna el estado operativo (Real vs Mockup).
     *
     * @param bool $forceRefresh Forzar verificación sin usar caché
     * @return array [
     *     'is_valid' => bool,
     *     'mode' => 'real'|'mockup',
     *     'status_code' => int|null,
     *     'badge_label' => string,
     *     'badge_color' => string,
     *     'message' => string,
     *     'key_preview' => string,
     *     'checked_at' => string,
     *     'error' => string|null
     * ]
     */
    public function checkApiKeyStatus(bool $forceRefresh = false): array
    {
        $cacheKey = 'gemini_api_key_status_v1';

        if ($forceRefresh) {
            Cache::forget($cacheKey);
        }

        return Cache::remember($cacheKey, 180, function () {
            $apiKey = config('services.gemini.api_key', env('GEMINI_API_KEY'));

            if (empty($apiKey) || $apiKey === 'your_gemini_api_key_here') {
                return [
                    'is_valid' => false,
                    'mode' => 'mockup',
                    'status_code' => null,
                    'badge_label' => 'Modo Mockup',
                    'badge_color' => 'warning',
                    'message' => 'Sin API Key configurada. Las respuestas se generan en modo simulación.',
                    'key_preview' => 'No configurada',
                    'checked_at' => now()->format('H:i:s d/m/Y'),
                    'error' => null,
                ];
            }

            $keyPreview = strlen($apiKey) > 12 
                ? substr($apiKey, 0, 7) . '...' . substr($apiKey, -5)
                : '***';

            try {
                $endpoint = "{$this->baseUrl}/models?key={$apiKey}";
                $response = Http::timeout(6)->get($endpoint);

                if ($response->successful()) {
                    return [
                        'is_valid' => true,
                        'mode' => 'real',
                        'status_code' => $response->status(),
                        'badge_label' => 'Modo Real (API Gemini)',
                        'badge_color' => 'success',
                        'message' => 'API Key válida y conectada en vivo con Google Gemini.',
                        'key_preview' => $keyPreview,
                        'checked_at' => now()->format('H:i:s d/m/Y'),
                        'error' => null,
                    ];
                }

                return [
                    'is_valid' => false,
                    'mode' => 'mockup',
                    'status_code' => $response->status(),
                    'badge_label' => 'Modo Mockup (Key Inválida)',
                    'badge_color' => 'danger',
                    'message' => "La clave fue rechazada por Gemini (Código {$response->status()}). Se activa el modo simulación.",
                    'key_preview' => $keyPreview,
                    'checked_at' => now()->format('H:i:s d/m/Y'),
                    'error' => $response->json('error.message', 'Rechazo de credenciales'),
                ];
            } catch (Exception $e) {
                return [
                    'is_valid' => false,
                    'mode' => 'mockup',
                    'status_code' => 500,
                    'badge_label' => 'Modo Mockup (Error Red)',
                    'badge_color' => 'warning',
                    'message' => 'No fue posible conectar con Google Gemini. Se activa modo simulación.',
                    'key_preview' => $keyPreview,
                    'checked_at' => now()->format('H:i:s d/m/Y'),
                    'error' => $e->getMessage(),
                ];
            }
        });
    }

    /**
     * Ejecuta una petición a la API de Gemini utilizando la configuración almacenada en el modelo Agent.
     *
     * @param Agent $agent Modelo con la configuración del agente
     * @param string $prompt Texto del prompt o entrada para el agente
     * @return array [
     *     'text' => string,
     *     'duration_ms' => int,
     *     'tokens_used' => int|null,
     *     'is_simulated' => bool
     * ]
     * @throws Exception
     */
    public function generate(Agent $agent, string $prompt): array
    {
        $startTime = microtime(true);
        $apiKey = config('services.gemini.api_key', env('GEMINI_API_KEY'));

        // Si no hay API key configurada, operar en modo demostración/simulación controlada
        if (empty($apiKey) || $apiKey === 'your_gemini_api_key_here') {
            return $this->simulateResponse($agent, $prompt, $startTime);
        }

        $model = $this->resolveModelName($agent->modelo ?: 'gemini-2.5-flash');
        $endpoint = "{$this->baseUrl}/models/{$model}:generateContent?key={$apiKey}";

        $payload = [
            'contents' => [
                [
                    'role' => 'user',
                    'parts' => [
                        ['text' => $prompt],
                    ],
                ],
            ],
            'generationConfig' => [
                'temperature' => (float) $agent->temperatura,
                'maxOutputTokens' => (int) $agent->max_tokens,
            ],
        ];

        // System Instruction si está definido
        if (!empty($agent->system_instruction)) {
            $payload['system_instruction'] = [
                'parts' => [
                    ['text' => $agent->system_instruction],
                ],
            ];
        }

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->timeout(60)->post($endpoint, $payload);

            $durationMs = (int) round((microtime(true) - $startTime) * 1000);

            if ($response->failed()) {
                Log::error('Error en Gemini API', [
                    'agent_id' => $agent->id,
                    'agent_nombre' => $agent->nombre,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                // Si la cuota excedió o la clave es inválida, retornar fallback informativo con simulación
                if ($response->status() === 400 || $response->status() === 403 || $response->status() === 429) {
                    return [
                        'text' => "⚠️ [Aviso de API Gemini - Código {$response->status()}]: {$response->json('error.message', 'Error en la petición a Gemini')}\n\nRespuesta generada en modo Mockup del agente '{$agent->nombre}':\n" . $this->generateFallbackText($agent, $prompt),
                        'duration_ms' => $durationMs,
                        'tokens_used' => 0,
                        'is_simulated' => true,
                    ];
                }

                throw new Exception("Error al comunicar con Gemini ({$response->status()}): " . $response->body());
            }

            $data = $response->json();
            $generatedText = $data['candidates'][0]['content']['parts'][0]['text'] ?? '';
            $totalTokens = $data['usageMetadata']['totalTokenCount'] ?? null;

            return [
                'text' => trim($generatedText),
                'duration_ms' => $durationMs,
                'tokens_used' => $totalTokens,
                'is_simulated' => false,
            ];
        } catch (Exception $e) {
            Log::warning('Excepción en llamada directa a Gemini. Activando simulación de emergencia', [
                'agent' => $agent->nombre,
                'error' => $e->getMessage(),
            ]);

            return $this->simulateResponse($agent, $prompt, $startTime);
        }
    }

    /**
     * Simulación inteligente cuando aún no se ha provisto una GEMINI_API_KEY o falla la red.
     */
    protected function simulateResponse(Agent $agent, string $prompt, float $startTime): array
    {
        // Simular latencia de red realista (200ms - 400ms)
        usleep(rand(150000, 350000));
        $durationMs = (int) round((microtime(true) - $startTime) * 1000);

        $simulatedText = $this->generateFallbackText($agent, $prompt);

        return [
            'text' => $simulatedText,
            'duration_ms' => $durationMs,
            'tokens_used' => rand(120, 450),
            'is_simulated' => true,
        ];
    }

    protected function generateFallbackText(Agent $agent, string $prompt): string
    {
        $shortPrompt = mb_strimwidth(strip_tags($prompt), 0, 140, '...');
        return "✨ [Modo Mockup / Simulación | Agente: '{$agent->nombre}' | Modelo: {$agent->modelo}]\n\n" .
            "Procesando entrada: \"{$shortPrompt}\"\n\n" .
            "Como {$agent->nombre}, basándome en mi instrucción del sistema ({$agent->system_instruction}):\n\n" .
            "1. **Análisis Preliminar:** Se han evaluado los requerimientos del usuario con temperatura {$agent->temperatura}.\n" .
            "2. **Aporte Especializado:** Se estructura la solución aplicando las mejores prácticas y validaciones requeridas para este paso.\n" .
            "3. **Conclusión:** Se entrega la respuesta lista para el siguiente nodo del pipeline o como resultado consolidado.";
    }

    public function resolveModelName(string $model): string
    {
        return match ($model) {
            'gemini-1.5-flash', 'gemini-2.0-flash', 'gemini-2.0-flash-lite' => 'gemini-2.5-flash',
            'gemini-1.5-pro', 'gemini-2.0-pro-exp-02-05' => 'gemini-2.5-pro',
            default => $model,
        };
    }
}

