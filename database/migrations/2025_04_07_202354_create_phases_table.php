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
            $table->string('objections')->nullable();
            $table->dateTime('record_date');
            $table->enum('state', ['unsigned', 'signed']);
            $table->dateTime('sign_date')->nullable();
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
