<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Agent extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'descripcion',
        'modelo',
        'system_instruction',
        'temperatura',
        'max_tokens',
        'estado',
        'avatar_color',
    ];

    protected function casts(): array
    {
        return [
            'temperatura' => 'float',
            'max_tokens' => 'integer',
            'estado' => 'boolean',
        ];
    }

    public function tasks(): BelongsToMany
    {
        return $this->belongsToMany(Task::class, 'agent_task')
            ->withPivot('orden', 'rol_en_tarea')
            ->withTimestamps()
            ->orderByPivot('orden');
    }

    public function executions(): HasMany
    {
        return $this->hasMany(AgentExecution::class);
    }
}
