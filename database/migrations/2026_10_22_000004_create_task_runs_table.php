<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('task_runs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->longText('input_usuario');
            $table->enum('modo_ejecucion', ['secuencial', 'paralelo'])->default('secuencial');
            $table->longText('resultado_final')->nullable();
            $table->enum('estado', ['pendiente', 'en_proceso', 'completado', 'fallido'])->default('pendiente');
            $table->integer('duracion_ms')->default(0);
            $table->text('error')->nullable();
            $table->timestamp('iniciado_el')->nullable();
            $table->timestamp('finalizado_el')->nullable();
            $table->timestamps();
        });

        Schema::create('agent_executions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_run_id')->constrained()->cascadeOnDelete();
            $table->foreignId('agent_id')->constrained()->cascadeOnDelete();
            $table->integer('paso')->default(1);
            $table->longText('input_agente');
            $table->longText('system_instruction_usado')->nullable();
            $table->longText('output_agente')->nullable();
            $table->enum('estado', ['completado', 'fallido'])->default('completado');
            $table->integer('duracion_ms')->default(0);
            $table->integer('tokens_utilizados')->nullable();
            $table->text('error')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agent_executions');
        Schema::dropIfExists('task_runs');
    }
};
