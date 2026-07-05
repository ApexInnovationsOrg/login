<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEvaluationQuestionsTable extends Migration
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
        Schema::create('EvaluationQuestions', function (Blueprint $table) {
            $table->increments('ID');
            $table->integer('CourseID')->nullable();
            $table->integer('OrganizationID')->nullable();
            $table->integer('ListOrder')->nullable();
            $table->string('Question')->default('');
            $table->string('Abbreviation')->default('');
            $table->integer('Type')->default(0);
            $table->enum('Active', ['Y', 'N'])->default('Y');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('EvaluationQuestions');
    }
}
