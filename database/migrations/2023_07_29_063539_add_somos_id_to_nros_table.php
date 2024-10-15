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
        Schema::table('nros', function (Blueprint $table) {
            $table->unsignedBigInteger('somos_id');
                
            $table->foreign('somos_id')->references('id')->on('somos')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('nros', function (Blueprint $table) {
            $table->dropForeign(['somos_id']);

            $table->dropColumn('somos_id');
        });
    }
};
