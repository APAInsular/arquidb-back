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
        Schema::disableForeignKeyConstraints();

        Schema::create('expedients', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->char('number', 8);
            $table->dateTime('start_date');
            $table->dateTime('end_date')->nullable();
            $table->string('description')->nullable();
            $table->string('site');
            $table->char('postal_code', 5)->nullable();
            $table->decimal('budget', 9, 2)->nullable();
            $table->timestamps();
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expedients');
    }
};
