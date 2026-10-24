@php
    $apiStatus = app(\App\Services\GeminiAgentService::class)->checkApiKeyStatus();
@endphp

<div class="flex items-center gap-2 mr-3 py-1">
    @if($apiStatus['mode'] === 'real')
        <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full text-xs font-semibold bg-emerald-500/10 text-emerald-400 border border-emerald-500/30 shadow-sm"
             title="{{ $apiStatus['message'] }} (Verificado: {{ $apiStatus['checked_at'] }})">
            <span class="relative flex h-2 w-2">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
            </span>
            <span class="tracking-wide">Gemini: <strong class="text-white">Modo Real</strong></span>
            <span class="font-mono text-[10px] text-emerald-300/80 bg-emerald-950/60 px-1.5 py-0.5 rounded border border-emerald-800/40">
                {{ $apiStatus['key_preview'] }}
            </span>
        </div>
    @else
        <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full text-xs font-semibold bg-amber-500/10 text-amber-400 border border-amber-500/30 shadow-sm"
             title="{{ $apiStatus['message'] }} (Verificado: {{ $apiStatus['checked_at'] }})">
            <span class="relative flex h-2 w-2">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
                <span class="relative inline-flex rounded-full h-2 w-2 bg-amber-500"></span>
            </span>
            <span class="tracking-wide">Gemini: <strong class="text-white">Modo Mockup</strong></span>
            <span class="text-[10px] text-amber-300/80 bg-amber-950/60 px-1.5 py-0.5 rounded border border-amber-800/40">
                Simulación
            </span>
        </div>
    @endif
</div>
