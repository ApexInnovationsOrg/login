<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEssayAnswersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * Schema mirrors the shared production database (maintained by hand);
     * these migrations exist for local development and testing only and
     * must never be run against the shared production database.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('EssayAnswers', function (Blueprint $table) {
            $table->increments('ID');
            $table->integer('TestResultID')->default(0);
            $table->integer('EssayQuestionID')->default(0);
            $table->string('Answer')->nullable();
            $table->integer('Score')->nullable();
            $table->enum('Reviewed', ['Y', 'N'])->default('Y');
            $table->integer('ReviewedBy')->nullable();
            $table->dateTime('ReviewedDate')->nullable();
            $table->text('Note')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('EssayAnswers');
    }
}
