<?php

use App\Models\Agent;
use App\Models\Task;
use App\Services\GeminiAgentService;
use Illuminate\Support\Facades\Route;

Route::get('/', function (GeminiAgentService $geminiService) {
    $agents = Agent::where('estado', true)->take(6)->get();
    $tasks = Task::where('estado', true)->with('agents')->take(4)->get();
    $apiStatus = $geminiService->checkApiKeyStatus();

    return view('welcome', compact('agents', 'tasks', 'apiStatus'));
});
