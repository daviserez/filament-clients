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
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            $table
                ->foreignId('team_id')
                ->constrained('users')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->string('name');
            $table->string('firstname')->nullable();
            $table->string('primary_phone')->nullable();
            $table->string('secondary_phone')->nullable();
            $table->string('street')->nullable();
            $table->string('postcode')->nullable();
            $table->string('city')->nullable();
            $table->string('country')->nullable();
            $table->string('email')->unique()->nullable();
            $table->text('notes')->nullable();
            $table->string('avatar_color');
            $table->softDeletes();

            $table->index('name');
            $table->index('firstname');

            $table->index(['firstname', 'name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
