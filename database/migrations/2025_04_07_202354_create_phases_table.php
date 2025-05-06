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

        Schema::create('phases', function (Blueprint $table) {
            $table->id();
            $table->string('phase', 4);
            $table->string('title');
            $table->string('observations')->nullable();
            $table->enum('state', ['unsigned', 'signed']);
            $table->dateTime('start_date');
            $table->dateTime('record_date');
            $table->foreignId('expedient_id')->constrained()->onDelete('restrict');
            $table->timestamps();
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('phases');
    }
};
