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
        Schema::table('commerce_seal', function (Blueprint $table) {
            $table->string('state')->default('none'); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('commerce_seal', function (Blueprint $table) {
            $table->dropColumn('state');
        });
    }
};
