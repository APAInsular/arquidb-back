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
        Schema::table('expedients', function (Blueprint $table) {
            // Cambiar el tipo de columna
            $table->string('title')->nullable()->change();
            $table->string('site')->nullable()->change();
            $table->string('postal_code', 6)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('expedients', function (Blueprint $table) {
            // Revertir al tipo original
            $table->string('title')->change();
            $table->string('site')->change();
            $table->char('postal_code', 5)->nullable()->change();
        });
    }
};
