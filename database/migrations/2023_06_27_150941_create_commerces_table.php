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
        Schema::create('commerces', function (Blueprint $table) {
            $table->id();

            $table->string('name')->nullable();
            $table->text('description')->nullable();
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('plz')->nullable();
            $table->string('email')->nullable();
            $table->string('phone_number')->nullable();
            $table->string('website')->nullable();
            $table->time('opening_time')->nullable();
            $table->time('closing_time')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->decimal('points', 8, 2)->nullable();
            $table->decimal('percent', 5, 2)->nullable();
			$table->double('donated_points')->default(0);
			$table->double('gived_points')->default(0);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('commerces');
    }
};
