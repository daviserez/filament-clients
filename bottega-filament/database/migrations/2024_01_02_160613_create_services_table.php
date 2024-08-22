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
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            $table
                ->foreignId('team_id')
                ->constrained('users')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->string('name');
            $table->unsignedInteger('price')->default(0);

            $table->softDeletes();
            $table->index('name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};
