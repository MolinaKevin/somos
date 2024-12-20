<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('commerce_seal')
            ->where('state', 'none')
            ->update(['state' => 0]);

        DB::table('commerce_seal')
            ->where('state', 'partial')
            ->update(['state' => 1]);

        DB::table('commerce_seal')
            ->where('state', 'full')
            ->update(['state' => 2]);

        Schema::table('commerce_seal', function (Blueprint $table) {
            $table->unsignedTinyInteger('state')->default(0)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('int_seal', function (Blueprint $table) {
            $table->string('state')->default('none')->change();
        });

         DB::table('commerce_seal')
            ->where('state', 0)
            ->update(['state' => 'none']);

        DB::table('commerce_seal')
            ->where('state', 1)
            ->update(['state' => 'partial']);

        DB::table('commerce_seal')
            ->where('state', 2)
            ->update(['state' => 'full']);
    }
};
