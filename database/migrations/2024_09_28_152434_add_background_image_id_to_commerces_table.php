<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('commerces', function (Blueprint $table) {
            $table->dropColumn('background_image');
            $table->unsignedBigInteger('background_image_id')->nullable()->after('avatar');
            $table->foreign('background_image_id')->references('id')->on('fotos')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('commerces', function (Blueprint $table) {
            $table->string('background_image')->nullable();
            $table->dropForeign(['background_image_id']);
            $table->dropColumn('background_image_id');
        });
    }


};
