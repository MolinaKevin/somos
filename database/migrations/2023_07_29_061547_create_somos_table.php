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
        Schema::create('somos', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->text('description')->nullable();
			$table->string('address');
            $table->string('city');
            $table->string('plz');

			$table->string('email')->nullable();
            $table->string('phone_number')->nullable();
            $table->string('website')->nullable();
	
            $table->text('operating_hours')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();

            $table->double('points')->default(0);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('somos');
    }
};
