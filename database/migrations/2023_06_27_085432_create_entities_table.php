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
        Schema::create('entities', function (Blueprint $table) {
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
            $table->double('percent')->default(10);

			$table->unsignedBigInteger('entityable_id')->nullable();
            $table->string('entityable_type')->nullable();		
	
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('entities');
    }
};
