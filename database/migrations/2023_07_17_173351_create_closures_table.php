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
        Schema::create('closures', function (Blueprint $table) {
            $table->id();

			$table->double('donated_points')->default(0);
			$table->double('gived_points')->default(0);
			$table->double('points')->default(0);

            $table->unsignedBigInteger('commerce_id');
            $table->foreign('commerce_id')->references('id')->on('commerces')->onDelete('cascade');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('closures');
    }
};
