<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TaskRun extends Model
{
    use HasFactory;

    protected $fillable = [
        'task_id',
        'user_id',
        'input_usuario',
        'modo_ejecucion',
        'resultado_final',
        'estado',
        'duracion_ms',
        'error',
        'iniciado_el',
        'finalizado_el',
    ];

    protected function casts(): array
    {
        return [
            'duracion_ms' => 'integer',
            'iniciado_el' => 'datetime',
            'finalizado_el' => 'datetime',
        ];
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function agentExecutions(): HasMany
    {
        return $this->hasMany(AgentExecution::class)->orderBy('paso');
    }
}
