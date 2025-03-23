<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('action_traces', function (Blueprint $table) {
            $table->id();
            $table->nullableMorphs('actor');
            $table->nullableMorphs('target');
            $table->string('action');
            $table->json('params')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('action_traces');
    }
};