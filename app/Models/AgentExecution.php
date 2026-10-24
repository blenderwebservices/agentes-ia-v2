<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AgentExecution extends Model
{
    use HasFactory;

    protected $fillable = [
        'task_run_id',
        'agent_id',
        'paso',
        'input_agente',
        'system_instruction_usado',
        'output_agente',
        'estado',
        'duracion_ms',
        'tokens_utilizados',
        'error',
    ];

    protected function casts(): array
    {
        return [
            'paso' => 'integer',
            'duracion_ms' => 'integer',
            'tokens_utilizados' => 'integer',
        ];
    }

    public function taskRun(): BelongsTo
    {
        return $this->belongsTo(TaskRun::class);
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(Agent::class);
    }
}
