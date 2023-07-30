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
        Schema::create('points_purchases', function (Blueprint $table) {
            $table->id();

			$table->double('amount')->default(0.0);
			$table->double('points')->default(0.0);
	
			$table->uuid('uuid')->unique(); 

			$table->unsignedBigInteger('user_id')->nullable();
			$table->foreign('user_id')->references('id')->on('users');

			$table->unsignedBigInteger('commerce_id');
			$table->foreign('commerce_id')->references('id')->on('commerces');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('points_purchases');
    }
};
