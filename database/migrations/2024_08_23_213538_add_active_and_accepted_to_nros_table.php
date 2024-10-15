<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddActiveAndAcceptedToNrosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('nros', function (Blueprint $table) {
            $table->boolean('active')->default(false);
            $table->boolean('accepted')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('nros', function (Blueprint $table) {
            $table->dropColumn('active');
            $table->dropColumn('accepted');
        });
    }
}

