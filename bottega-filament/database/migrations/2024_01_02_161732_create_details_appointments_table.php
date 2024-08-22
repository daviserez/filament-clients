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
        Schema::create('details_appointments', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            $table
                ->foreignId('appointment_id')
                ->constrained()
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table
                ->foreignId('service_id')
                ->constrained()
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->unsignedInteger('price')->default(0);
            $table->unsignedInteger('service_price')->default(0);
            $table->string('color')->nullable();
            $table->text('comment')->nullable();

            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('details_appointments');
    }
};
