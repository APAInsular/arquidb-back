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
            $table->date('birth_date')->nullable();
            $table->string('nationality')->nullable();
            $table->string('banking_entity')->nullable();
            $table->char('account_number', 24)->nullable();
            $table->string('college')->nullable();
            $table->string('origin_college')->nullable();
            $table->string('origin_college_number')->nullable();
            $table->enum('degree', ["Technical Architect"]);
            $table->string('collegiate_number')->nullable();
            $table->string('specialty')->nullable();
            $table->date('termination_date')->nullable();
            $table->date('graduation_date')->nullable();
            $table->string('career_end_et')->nullable();
            $table->string('web_page')->nullable();
            $table->string('council_reg_number')->nullable();
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
