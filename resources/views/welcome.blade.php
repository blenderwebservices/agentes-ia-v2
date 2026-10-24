<!DOCTYPE html>
<html lang="es" class="scroll-smooth dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>MultiAgent Studio — Plataforma Multiagente IA con Laravel 11 & Gemini</title>
    <meta name="description" content="Plataforma web multiagente impulsada nativamente por Laravel 11, FilamentPHP v3 y la API de Google Gemini. Colaboración secuencial y concurrente de agentes especializados.">

    <!-- Google Fonts: Inter & Outfit -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Tailwind Play CDN for high fidelity rendering -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                        display: ['Outfit', 'sans-serif'],
                    },
                    colors: {
                        brand: {
                            50: '#eef2ff',
                            100: '#e0e7ff',
                            500: '#6366f1',
                            600: '#4f46e5',
                            700: '#4338ca',
                            900: '#1e1b4b',
                        }
                    }
                }
            }
        }
    </script>
    <style>
        .glass {
            background: rgba(17, 24, 39, 0.7);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.08);
        }
        .glow-effect {
            background: radial-gradient(circle at 50% 0%, rgba(99, 102, 241, 0.25) 0%, rgba(0, 0, 0, 0) 70%);
        }
    </style>
</head>
<body class="bg-[#0b0f19] text-slate-200 font-sans antialiased selection:bg-indigo-500 selection:text-white">

    <!-- Glow background -->
    <div class="fixed top-0 left-1/2 -translate-x-1/2 w-full max-w-7xl h-[600px] glow-effect pointer-events-none -z-10"></div>

    <!-- Barra de Navegación -->
    <header class="sticky top-0 z-50 glass border-b border-slate-800/80">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-20 flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-indigo-600 via-violet-600 to-cyan-400 flex items-center justify-center shadow-lg shadow-indigo-500/25">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                    </svg>
                </div>
                <div>
                    <span class="text-xl font-display font-bold text-white tracking-tight">MultiAgent<span class="text-indigo-400">.Studio</span></span>
                    <span class="hidden sm:inline-block ml-2 px-2 py-0.5 text-xs font-semibold rounded-full bg-indigo-500/10 text-indigo-400 border border-indigo-500/20">Gemini Native</span>
                </div>

                <!-- Indicador de API Key: Real vs Mockup -->
                <div class="hidden sm:flex items-center ml-2">
                    @if(isset($apiStatus) && $apiStatus['mode'] === 'real')
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium bg-emerald-500/10 text-emerald-400 border border-emerald-500/30 shadow-sm shadow-emerald-500/20" title="{{ $apiStatus['message'] }}">
                            <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                            <span>API Gemini: <strong class="text-white">Modo Real</strong></span>
                            <span class="text-[10px] text-emerald-300/80 font-mono hidden md:inline">({{ $apiStatus['key_preview'] }})</span>
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium bg-amber-500/10 text-amber-400 border border-amber-500/30 shadow-sm shadow-amber-500/20" title="{{ $apiStatus['message'] ?? 'Modo Mockup' }}">
                            <span class="w-2 h-2 rounded-full bg-amber-400 animate-pulse"></span>
                            <span>API Gemini: <strong class="text-white">Modo Mockup</strong></span>
                            <span class="text-[10px] text-amber-300/80 hidden md:inline">(Simulado)</span>
                        </span>
                    @endif
                </div>
            </div>

            <nav class="hidden md:flex items-center space-x-8 text-sm font-medium text-slate-300">
                <a href="#como-funciona" class="hover:text-white transition">Cómo Funciona</a>
                <a href="#agentes" class="hover:text-white transition">Agentes IA</a>
                <a href="#arquitectura" class="hover:text-white transition">Arquitectura PHP</a>
                <a href="#demostracion" class="hover:text-white transition">Simulador</a>
            </nav>

            <div class="flex items-center space-x-4">
                <a href="{{ url('/admin/login') }}" class="text-sm font-medium text-slate-300 hover:text-white transition px-3 py-2">
                    Iniciar Sesión
                </a>
                <a href="{{ url('/admin') }}" class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-indigo-600 to-violet-600 hover:from-indigo-500 hover:to-violet-500 text-white font-medium text-sm shadow-lg shadow-indigo-600/30 hover:shadow-indigo-600/50 transition-all duration-200">
                    Acceder al Panel
                </a>
            </div>
        </div>
    </header>

    <main>
        <!-- Hero Section -->
        <section class="pt-24 pb-20 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto text-center relative">
            <div class="inline-flex flex-wrap items-center justify-center gap-2 mb-8">
                <span class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full glass border border-indigo-500/30 text-indigo-300 text-xs font-semibold">
                    <span class="w-2 h-2 rounded-full bg-indigo-400 animate-ping"></span>
                    Laravel 11 + FilamentPHP v3 + Google Gemini API 100% Nativo
                </span>

                @if(isset($apiStatus) && $apiStatus['mode'] === 'real')
                    <span class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full glass border border-emerald-500/40 text-emerald-300 text-xs font-semibold shadow-lg shadow-emerald-500/10">
                        <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                        <span>Motor: <strong>En Vivo con API Gemini</strong> (Status {{ $apiStatus['status_code'] }} OK | Key: {{ $apiStatus['key_preview'] }})</span>
                    </span>
                @else
                    <span class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full glass border border-amber-500/40 text-amber-300 text-xs font-semibold shadow-lg shadow-amber-500/10">
                        <span class="w-2 h-2 rounded-full bg-amber-400"></span>
                        <span>Motor: <strong>Modo Mockup / Simulación</strong> (Sin API Key válida)</span>
                    </span>
                @endif
            </div>

            <h1 class="text-4xl sm:text-6xl lg:text-7xl font-display font-extrabold text-white tracking-tight leading-[1.1] max-w-4xl mx-auto">
                Orquestación Multiagente de IA <span class="text-transparent bg-clip-text bg-gradient-to-r from-indigo-400 via-purple-300 to-cyan-400">Sin Salir de PHP</span>
            </h1>

            <p class="mt-6 text-lg sm:text-xl text-slate-400 max-w-2xl mx-auto leading-relaxed">
                Diseña, coordina y ejecuta enjambres de agentes de Gemini especializados. Colaboración en cadena secuencial o resolución en paralelo con trazabilidad y métricas exactas.
            </p>

            <div class="mt-10 flex flex-col sm:flex-row items-center justify-center gap-4">
                <a href="{{ url('/admin') }}" class="w-full sm:w-auto px-8 py-4 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-semibold shadow-xl shadow-indigo-600/30 hover:shadow-indigo-600/50 transition-all text-base flex items-center justify-center gap-2">
                    <span>Explorar Panel de Control</span>
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                </a>
                <a href="#demostracion" class="w-full sm:w-auto px-8 py-4 rounded-xl glass hover:bg-slate-800 text-slate-200 font-semibold border border-slate-700 transition-all text-base">
                    Ver Demostración Interactiva
                </a>
            </div>

            <!-- Métricas / Sellos -->
            <div class="mt-16 grid grid-cols-2 md:grid-cols-4 gap-6 max-w-4xl mx-auto border-t border-slate-800/80 pt-10 text-center">
                <div>
                    <div class="text-2xl sm:text-3xl font-display font-bold text-white">100% PHP</div>
                    <div class="text-xs sm:text-sm text-slate-400 mt-1">Cero dependencias en Python</div>
                </div>
                <div>
                    <div class="text-2xl sm:text-3xl font-display font-bold text-indigo-400">Gemini 1.5 & 2.0</div>
                    <div class="text-xs sm:text-sm text-slate-400 mt-1">Flash, Pro y Modelos Multimodales</div>
                </div>
                <div>
                    <div class="text-2xl sm:text-3xl font-display font-bold text-emerald-400">Dual Mode</div>
                    <div class="text-xs sm:text-sm text-slate-400 mt-1">Secuencial & Paralelo</div>
                </div>
                <div>
                    <div class="text-2xl sm:text-3xl font-display font-bold text-purple-400">Filament v3</div>
                    <div class="text-xs sm:text-sm text-slate-400 mt-1">Admin Panel & RBAC Integrado</div>
                </div>
            </div>
        </section>

        <!-- Sección: Cómo Funciona la Colaboración Multiagente -->
        <section id="como-funciona" class="py-20 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto">
            <div class="text-center max-w-3xl mx-auto mb-16">
                <h2 class="text-xs font-bold tracking-widest text-indigo-400 uppercase">Flujo de Trabajo</h2>
                <p class="mt-2 text-3xl sm:text-4xl font-display font-bold text-white">¿Cómo Colaboran los Agentes?</p>
                <p class="mt-4 text-slate-400">En lugar de pedirle a un solo modelo genérico que resuelva todo de golpe, la plataforma divide el problema en roles expertos altamente dirigidos.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <!-- Modo Secuencial -->
                <div class="glass p-8 rounded-2xl relative overflow-hidden group hover:border-indigo-500/50 transition">
                    <div class="w-12 h-12 rounded-xl bg-indigo-500/10 text-indigo-400 flex items-center justify-center mb-6 border border-indigo-500/20">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7"/></svg>
                    </div>
                    <span class="text-xs font-semibold text-indigo-400 uppercase tracking-wider">Modo 01</span>
                    <h3 class="text-2xl font-display font-bold text-white mt-1">Orquestación Secuencial (Pipeline)</h3>
                    <p class="mt-3 text-slate-400 text-sm leading-relaxed">
                        El resultado generado por el <strong>Agente 1</strong> (ej. Redactor o Investigador) se canaliza directamente como entrada contextual para el <strong>Agente 2</strong> (ej. Revisor Técnico o Auditor), refinando el resultado de forma acumulativa y estructurada.
                    </p>

                    <div class="mt-6 space-y-3">
                        <div class="flex items-center gap-3 p-3 rounded-lg bg-slate-900/80 border border-slate-800 text-xs">
                            <span class="px-2 py-0.5 rounded bg-indigo-900 text-indigo-300 font-mono font-bold">Paso 1</span>
                            <span class="text-slate-300 font-medium">Agente Investigador genera el bosquejo conceptual.</span>
                        </div>
                        <div class="flex items-center gap-3 p-3 rounded-lg bg-slate-900/80 border border-slate-800 text-xs">
                            <span class="px-2 py-0.5 rounded bg-indigo-900 text-indigo-300 font-mono font-bold">Paso 2</span>
                            <span class="text-slate-300 font-medium">Agente Redactor expande y redacta la solución.</span>
                        </div>
                        <div class="flex items-center gap-3 p-3 rounded-lg bg-slate-900/80 border border-slate-800 text-xs">
                            <span class="px-2 py-0.5 rounded bg-emerald-900 text-emerald-300 font-mono font-bold">Paso 3</span>
                            <span class="text-slate-300 font-medium">Agente Auditor verifica calidad, sintaxis y estilo final.</span>
                        </div>
                    </div>
                </div>

                <!-- Modo Paralelo -->
                <div class="glass p-8 rounded-2xl relative overflow-hidden group hover:border-purple-500/50 transition">
                    <div class="w-12 h-12 rounded-xl bg-purple-500/10 text-purple-400 flex items-center justify-center mb-6 border border-purple-500/20">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                    </div>
                    <span class="text-xs font-semibold text-purple-400 uppercase tracking-wider">Modo 02</span>
                    <h3 class="text-2xl font-display font-bold text-white mt-1">Orquestación Paralela (Enjambre Concurrente)</h3>
                    <p class="mt-3 text-slate-400 text-sm leading-relaxed">
                        Múltiples agentes de Gemini reciben el input del usuario simultáneamente, analizando el problema desde diferentes perspectivas (Seguridad, Experiencia de Usuario, Eficiencia de Costes) y generando una síntesis combinada.
                    </p>

                    <div class="mt-6 space-y-3">
                        <div class="grid grid-cols-2 gap-2 text-xs">
                            <div class="p-3 rounded-lg bg-slate-900/80 border border-slate-800">
                                <span class="text-purple-300 font-bold block">🛡️ Agente Seguridad</span>
                                <span class="text-slate-400 text-[11px]">Evalúa vulnerabilidades OWASP</span>
                            </div>
                            <div class="p-3 rounded-lg bg-slate-900/80 border border-slate-800">
                                <span class="text-cyan-300 font-bold block">⚡ Agente Performance</span>
                                <span class="text-slate-400 text-[11px]">Optimiza consultas y algoritmos</span>
                            </div>
                        </div>
                        <div class="p-3 rounded-lg bg-emerald-950/40 border border-emerald-500/30 text-xs text-emerald-300 flex items-center gap-2">
                            <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                            <span>Consolidación automática en un único dictamen estructurado.</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Simulador Interactivo de Orquestación -->
        <section id="demostracion" class="py-20 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto">
            <div class="glass p-8 sm:p-12 rounded-3xl border border-indigo-500/30 relative">
                <div class="max-w-3xl mb-8">
                    <span class="text-xs font-bold tracking-widest text-indigo-400 uppercase">Prueba en Vivo</span>
                    <h2 class="text-3xl font-display font-bold text-white mt-1">Simulador de Pipeline Multiagente</h2>
                    <p class="text-slate-400 text-sm mt-2">Observa cómo los agentes se transfieren el contexto paso a paso en tiempo real.</p>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Entrada de prueba -->
                    <div class="lg:col-span-1 space-y-4">
                        <label class="block text-xs font-semibold text-slate-300 uppercase">Selecciona o escribe un caso:</label>
                        <div class="flex flex-col gap-2">
                            <button onclick="setPrompt('Diseñar un sistema de autenticación de doble factor (2FA) para Laravel con códigos TOTP.')" class="text-left p-3 rounded-xl bg-slate-900/90 border border-slate-800 hover:border-indigo-500 text-xs text-slate-300 transition">
                                🔒 2FA con TOTP en Laravel 11
                            </button>
                            <button onclick="setPrompt('Escribir un artículo técnico sobre orquestación de agentes de IA con arquitectura hexagonal en PHP.')" class="text-left p-3 rounded-xl bg-slate-900/90 border border-slate-800 hover:border-indigo-500 text-xs text-slate-300 transition">
                                📝 Artículo Técnico & Arquitectura
                            </button>
                            <button onclick="setPrompt('Refactorizar una consulta N+1 pesada en Eloquent con más de 100,000 registros.')" class="text-left p-3 rounded-xl bg-slate-900/90 border border-slate-800 hover:border-indigo-500 text-xs text-slate-300 transition">
                                ⚡ Optimización de Consultas Eloquent
                            </button>
                        </div>

                        <textarea id="demo-input" rows="3" class="w-full bg-slate-950 border border-slate-800 rounded-xl p-3 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-indigo-500" placeholder="Escribe tu propio prompt..."></textarea>

                        <button id="btn-run-demo" onclick="runDemoPipeline()" class="w-full py-3 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-semibold text-sm transition flex items-center justify-center gap-2">
                            <span>▶ Ejecutar Pipeline Secuencial</span>
                        </button>
                    </div>

                    <!-- Visualizador interactivo de pasos -->
                    <div class="lg:col-span-2 bg-slate-950/80 rounded-2xl p-6 border border-slate-800/80 flex flex-col justify-between min-h-[360px]">
                        <div id="demo-steps" class="space-y-4">
                            <div class="flex items-center gap-3 text-slate-500 text-sm">
                                <span class="w-2.5 h-2.5 rounded-full bg-slate-700"></span>
                                <span>Esperando que inicies una simulación...</span>
                            </div>
                        </div>

                        <div id="demo-result" class="hidden mt-6 p-4 rounded-xl bg-indigo-950/30 border border-indigo-500/30 text-xs text-slate-200">
                            <div class="font-bold text-indigo-400 mb-1 flex items-center justify-between">
                                <span>🎉 Resultado Final Consolidado</span>
                                <span class="text-[10px] text-slate-400">Completado en 420 ms</span>
                            </div>
                            <p id="demo-result-text" class="text-slate-300"></p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Agentes Disponibles en la Base de Datos -->
        <section id="agentes" class="py-20 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto">
            <div class="flex flex-col sm:flex-row sm:items-end justify-between mb-12">
                <div>
                    <h2 class="text-xs font-bold tracking-widest text-indigo-400 uppercase">Catálogo Activo</h2>
                    <p class="mt-2 text-3xl font-display font-bold text-white">Agentes Especializados Configurados</p>
                </div>
                <a href="{{ url('/admin') }}" class="mt-4 sm:mt-0 text-sm text-indigo-400 hover:text-indigo-300 font-semibold flex items-center gap-1">
                    <span>Gestionar en Filament</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                @forelse($agents as $agent)
                    <div class="glass p-6 rounded-2xl border border-slate-800 hover:border-indigo-500/40 transition">
                        <div class="flex items-center justify-between mb-4">
                            <div class="w-10 h-10 rounded-xl flex items-center justify-center font-bold text-white shadow-md" style="background-color: {{ $agent->avatar_color }}">
                                {{ substr($agent->nombre, 0, 2) }}
                            </div>
                            <span class="px-2.5 py-1 rounded-full text-xs font-mono font-medium bg-slate-800 text-indigo-300 border border-slate-700">
                                {{ $agent->modelo }}
                            </span>
                        </div>
                        <h3 class="text-lg font-display font-bold text-white">{{ $agent->nombre }}</h3>
                        <p class="text-slate-400 text-xs mt-2 line-clamp-2">{{ $agent->descripcion ?: 'Agente especializado en resolución de tareas mediante Google Gemini.' }}</p>

                        <div class="mt-4 pt-4 border-t border-slate-800/80 flex items-center justify-between text-xs text-slate-500">
                            <span>Temp: <strong class="text-slate-300">{{ $agent->temperatura }}</strong></span>
                            <span>Tokens: <strong class="text-slate-300">{{ $agent->max_tokens }}</strong></span>
                        </div>
                    </div>
                @empty
                    <!-- Fallback si aún no se ejecutan los seeders -->
                    <div class="glass p-6 rounded-2xl border border-slate-800">
                        <div class="flex items-center justify-between mb-4">
                            <div class="w-10 h-10 rounded-xl bg-indigo-600 flex items-center justify-center font-bold text-white">RE</div>
                            <span class="px-2.5 py-1 rounded-full text-xs font-mono font-medium bg-slate-800 text-indigo-300">gemini-1.5-flash</span>
                        </div>
                        <h3 class="text-lg font-display font-bold text-white">Redactor Técnico</h3>
                        <p class="text-slate-400 text-xs mt-2">Especialista en redactar explicaciones didácticas, documentación limpia y artículos de software.</p>
                    </div>
                    <div class="glass p-6 rounded-2xl border border-slate-800">
                        <div class="flex items-center justify-between mb-4">
                            <div class="w-10 h-10 rounded-xl bg-purple-600 flex items-center justify-center font-bold text-white">AU</div>
                            <span class="px-2.5 py-1 rounded-full text-xs font-mono font-medium bg-slate-800 text-purple-300">gemini-1.5-pro</span>
                        </div>
                        <h3 class="text-lg font-display font-bold text-white">Auditor de Código & Seguridad</h3>
                        <p class="text-slate-400 text-xs mt-2">Analiza código fuente contra vulnerabilidades, valida tipos estrictos y estándares PSR-12.</p>
                    </div>
                    <div class="glass p-6 rounded-2xl border border-slate-800">
                        <div class="flex items-center justify-between mb-4">
                            <div class="w-10 h-10 rounded-xl bg-emerald-600 flex items-center justify-center font-bold text-white">OP</div>
                            <span class="px-2.5 py-1 rounded-full text-xs font-mono font-medium bg-slate-800 text-emerald-300">gemini-2.0-flash</span>
                        </div>
                        <h3 class="text-lg font-display font-bold text-white">Optimizador de Base de Datos</h3>
                        <p class="text-slate-400 text-xs mt-2">Especializado en indexación, planes de consulta (EXPLAIN) y rendimiento de Eloquent ORM.</p>
                    </div>
                @endforelse
            </div>
        </section>

        <!-- Arquitectura Técnica -->
        <section id="arquitectura" class="py-20 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto border-t border-slate-800/80">
            <div class="text-center max-w-3xl mx-auto mb-16">
                <h2 class="text-xs font-bold tracking-widest text-indigo-400 uppercase">Ingeniería</h2>
                <p class="mt-2 text-3xl font-display font-bold text-white">Arquitectura 100% Nativa en Laravel</p>
                <p class="mt-4 text-slate-400 text-sm">Diseñado cumpliendo estrictamente la directiva de cero dependencias en Python o microservicios externos.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 text-sm">
                <div class="glass p-6 rounded-2xl border border-slate-800">
                    <div class="text-indigo-400 font-mono text-xs font-bold mb-2">01 / CAPA HTTP PURA</div>
                    <h3 class="text-lg font-bold text-white mb-2">GeminiAgentService</h3>
                    <p class="text-slate-400 text-xs leading-relaxed">
                        Lee directamente el modelo de la BD, estructura el payload JSON de Gemini (system_instruction, temperature, maxTokens) y despacha la petición con <code class="text-indigo-300">Illuminate\Support\Facades\Http</code>.
                    </p>
                </div>
                <div class="glass p-6 rounded-2xl border border-slate-800">
                    <div class="text-purple-400 font-mono text-xs font-bold mb-2">02 / ORQUESTADOR</div>
                    <h3 class="text-lg font-bold text-white mb-2">MultiAgentOrchestrator</h3>
                    <p class="text-slate-400 text-xs leading-relaxed">
                        Controla el encadenamiento de resultados (Step 1 ➔ Step 2) o la resolución paralela mediante <code class="text-purple-300">Http::pool</code>, auditando la latencia en milisegundos y los tokens utilizados.
                    </p>
                </div>
                <div class="glass p-6 rounded-2xl border border-slate-800">
                    <div class="text-cyan-400 font-mono text-xs font-bold mb-2">03 / PANEL DE CONTROL</div>
                    <h3 class="text-lg font-bold text-white mb-2">FilamentPHP v3</h3>
                    <p class="text-slate-400 text-xs leading-relaxed">
                        Gestión declarativa de Agentes, Tareas con asignación de orden y roles específicos, e historial de ejecuciones de solo lectura con vista de auditoría paso a paso.
                    </p>
                </div>
            </div>
        </section>

        <!-- CTA Final -->
        <section class="py-20 px-4 sm:px-6 lg:px-8 max-w-5xl mx-auto text-center">
            <div class="glass p-12 rounded-3xl border border-indigo-500/40 relative overflow-hidden">
                <div class="absolute -right-20 -bottom-20 w-64 h-64 bg-indigo-500/20 rounded-full blur-3xl pointer-events-none"></div>
                <h2 class="text-3xl sm:text-4xl font-display font-bold text-white">Comienza a Orquestar Agentes de IA Ahora</h2>
                <p class="mt-4 text-slate-300 text-base max-w-xl mx-auto">
                    Inicia sesión como Administrador o Usuario Estándar para configurar agentes, definir tareas y ejecutar pipelines colaborativos.
                </p>
                <div class="mt-8 flex flex-col sm:flex-row items-center justify-center gap-4">
                    <a href="{{ url('/admin/login') }}" class="w-full sm:w-auto px-8 py-3.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-semibold shadow-lg shadow-indigo-600/30 transition">
                        Acceder al Panel Administrativo
                    </a>
                    <a href="{{ url('/admin/register') }}" class="w-full sm:w-auto px-8 py-3.5 rounded-xl glass hover:bg-slate-800 text-slate-200 font-semibold border border-slate-700 transition">
                        Crear Cuenta de Usuario
                    </a>
                </div>
            </div>
        </section>
    </main>

    <footer class="border-t border-slate-800/80 py-8 text-center text-xs text-slate-500">
        <p>&copy; {{ date('Y') }} MultiAgent Studio. Desarrollado nativamente en Laravel 11 & FilamentPHP v3 con Google Gemini API.</p>
    </footer>

    <!-- Script del Simulador Interactivo -->
    <script>
        function setPrompt(text) {
            document.getElementById('demo-input').value = text;
        }

        async function runDemoPipeline() {
            const input = document.getElementById('demo-input').value.trim() || 'Diseñar un sistema de autenticación de doble factor (2FA) para Laravel.';
            const stepsContainer = document.getElementById('demo-steps');
            const resultContainer = document.getElementById('demo-result');
            const resultText = document.getElementById('demo-result-text');
            const runBtn = document.getElementById('btn-run-demo');

            runBtn.disabled = true;
            runBtn.classList.add('opacity-50');
            resultContainer.classList.add('hidden');

            stepsContainer.innerHTML = '';

            const pipeline = [
                { name: 'Agente Analista & Arquitecto', model: 'gemini-1.5-pro', action: 'Estructurando requerimientos y diagrama de flujo...' },
                { name: 'Agente Desarrollador Backend', model: 'gemini-1.5-flash', action: 'Generando modelos, controladores y clases de servicio...' },
                { name: 'Agente Auditor de Calidad & Seguridad', model: 'gemini-2.0-flash', action: 'Revisando validaciones criptográficas y PSR-12...' }
            ];

            for (let i = 0; i < pipeline.length; i++) {
                const step = pipeline[i];
                const stepElement = document.createElement('div');
                stepElement.className = 'flex items-start gap-3 p-3 rounded-xl bg-slate-900 border border-indigo-500/40 text-xs transition-all duration-300';
                stepElement.innerHTML = `
                    <div class="w-6 h-6 rounded-full bg-indigo-600/30 border border-indigo-500 text-indigo-300 flex items-center justify-center font-bold text-[10px] shrink-0 animate-spin">
                        ⏳
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="font-bold text-white">${step.name}</span>
                            <span class="px-1.5 py-0.5 rounded bg-slate-800 text-[10px] font-mono text-indigo-300">${step.model}</span>
                        </div>
                        <p class="text-slate-400 mt-1">${step.action}</p>
                    </div>
                `;
                stepsContainer.appendChild(stepElement);

                await new Promise(r => setTimeout(r, 600));

                stepElement.classList.remove('border-indigo-500/40');
                stepElement.classList.add('border-emerald-500/40', 'bg-emerald-950/20');
                stepElement.querySelector('.animate-spin').classList.remove('animate-spin');
                stepElement.querySelector('.shrink-0').innerHTML = '✓';
                stepElement.querySelector('.shrink-0').className = 'w-6 h-6 rounded-full bg-emerald-500/20 border border-emerald-500 text-emerald-400 flex items-center justify-center font-bold text-[10px] shrink-0';
            }

            resultText.innerText = `Solución generada para: "${input}". Los 3 agentes colaboraron en cadena: Arquitectura aprobada ➔ Código de servicio generado ➔ Auditoría de seguridad sin vulnerabilidades reportadas. Listo para producción.`;
            resultContainer.classList.remove('hidden');

            runBtn.disabled = false;
            runBtn.classList.remove('opacity-50');
        }
    </script>
</body>
</html>
