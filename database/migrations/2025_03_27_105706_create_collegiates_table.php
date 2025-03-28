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

        Schema::create('collegiates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('person_id')->constrained()->cascadeOnDelete();
            $table->date('birthDate')->nullable();
            $table->string('nationality')->nullable();
            $table->string('bankingEntity')->nullable();
            $table->char('accountNumber', 24)->nullable();
            $table->string('college')->nullable();
            $table->string('originCollege')->nullable();
            $table->string('originCollegeNumber')->nullable();
            $table->enum('degree', ["Technical Architect"]);
            $table->string('collegiateNumber')->nullable();
            $table->string('specialty')->nullable();
            $table->date('terminationDate')->nullable();
            $table->date('graduationDate')->nullable();
            $table->string('careerEndET')->nullable();
            $table->string('webPage')->nullable();
            $table->string('superiorCouncilRegNumber')->nullable();
            $table->string('situation')->nullable();
            $table->timestamps();
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('collegiates');
    }
};
