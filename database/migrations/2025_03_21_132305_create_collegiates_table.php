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
            $table->date('birthDate');
            $table->string('nationality');
            $table->string('bankingEntity');
            $table->char('accountNumber', 24);
            $table->string('college');
            $table->string('originCollege');
            $table->string('originCollegeNumber');
            $table->enum('degree', ["Technical Architect","Architect"]);
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
