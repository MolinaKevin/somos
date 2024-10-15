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
        Schema::create('contributions', function (Blueprint $table) {
            $table->id();

            $table->double('points');

			$table->unsignedBigInteger('somos_id')->nullable();
            $table->foreign('somos_id')->references('id')->on('somos')->onDelete('cascade');

			$table->unsignedBigInteger('nro_id')->nullable();
            $table->foreign('nro_id')->references('id')->on('nros')->onDelete('cascade');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contributions');
    }
};
