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
        Schema::create('agents', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->text('descripcion')->nullable();
            $table->string('modelo')->default('gemini-1.5-flash');
            $table->longText('system_instruction');
            $table->decimal('temperatura', 3, 2)->default(0.70);
            $table->integer('max_tokens')->default(2048);
            $table->boolean('estado')->default(true);
            $table->string('avatar_color')->default('#6366f1');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agents');
    }
};
