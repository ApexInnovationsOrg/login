<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNIHSSResultsTable extends Migration
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
        Schema::create('NIHSSResults', function (Blueprint $table) {
            $table->increments('ID');
            $table->integer('CourseID')->default(15);
            $table->integer('UserID')->default(0);
            $table->dateTime('TestStarted')->nullable(); // NOT NULL in prod (zero-date when omitted, non-strict MySQL)
            $table->string('Patient')->default('');
            $table->string('PatientList')->default('');
            $table->string('Patient1Answers')->nullable();
            $table->string('Patient2Answers')->nullable();
            $table->string('Patient3Answers')->nullable();
            $table->string('Patient4Answers')->nullable();
            $table->string('Patient5Answers')->nullable();
            $table->string('Patient6Answers')->nullable();
            $table->string('QuestionsAsked')->nullable();
            $table->string('AnswersGiven')->nullable();
            $table->dateTime('TestCompleted')->nullable();
            $table->integer('Score')->nullable();
            $table->enum('EvaluationCompleted', ['Y', 'N'])->default('N');
            $table->double('CEHoursClaimed')->nullable();
            $table->enum('ClearStatus', ['Y', 'N'])->default('N');
            $table->string('LicenseID')->nullable();
            $table->string('CredentialID')->nullable();
            $table->dateTime('SyncExtendedDate')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('NIHSSResults');
    }
}
