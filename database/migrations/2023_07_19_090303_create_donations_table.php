<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDonationsTable extends Migration
{
    public function up()
    {
        Schema::create('donations', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('commerce_id');
            $table->unsignedBigInteger('nro_id');
            $table->unsignedBigInteger('closure_id')->nullable();
            $table->decimal('amount', 8, 2);
            $table->decimal('donated_amount', 8, 2);
            $table->boolean('is_paid')->default(false);
            $table->timestamps();

            $table->foreign('commerce_id')->references('id')->on('commerces')->onDelete('cascade');
            $table->foreign('nro_id')->references('id')->on('nros')->onDelete('cascade');
            $table->foreign('closure_id')->references('id')->on('closures')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('donations');
    }
}

