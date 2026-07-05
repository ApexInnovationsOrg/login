<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTestResultsTable extends Migration
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
        Schema::create('TestResults', function (Blueprint $table) {
            $table->increments('ID');
            $table->integer('CourseID')->default(0);
            $table->integer('UserID')->default(0);
            $table->dateTime('TestStarted')->nullable(); // NOT NULL in prod (zero-date when omitted, non-strict MySQL)
            $table->dateTime('TestCompleted')->nullable();
            $table->integer('Score')->nullable();
            $table->enum('EvaluationCompleted', ['Y', 'N'])->default('N');
            $table->string('QuestionsAsked')->nullable();
            $table->string('AnswersGiven')->nullable();
            $table->double('CEHoursClaimed')->nullable();
            $table->enum('ClearStatus', ['Y', 'N'])->default('N');
            $table->integer('LicenseID')->nullable();
            $table->integer('CredentialID')->nullable();
            $table->dateTime('SyncExtendedDate')->nullable();
            $table->enum('Essay', ['Y', 'N'])->default('N');
            $table->enum('GradePending', ['Y', 'N'])->default('N');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('TestResults');
    }
}
