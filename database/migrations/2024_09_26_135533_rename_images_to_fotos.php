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
        Schema::rename('images', 'fotos');

        Schema::table('fotos', function (Blueprint $table) {
            $table->renameColumn('imageable_id', 'fotable_id');
            $table->renameColumn('imageable_type', 'fotable_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::rename('fotos', 'images');

        Schema::table('images', function (Blueprint $table) {
            $table->renameColumn('fotable_id', 'imageable_id');
            $table->renameColumn('fotable_type', 'imageable_type');
        });
    }
};
