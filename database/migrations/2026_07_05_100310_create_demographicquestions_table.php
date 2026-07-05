<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDemographicQuestionsTable extends Migration
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
        Schema::create('DemographicQuestions', function (Blueprint $table) {
            $table->increments('ID');
            $table->string('QuestionText')->default('');
            $table->integer('DemographicQuestionTypeID')->default(0);
            $table->enum('Active', ['Y', 'N']);
            $table->text('Notes')->nullable(); // NOT NULL in prod (implicit '' when omitted, non-strict MySQL)
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('DemographicQuestions');
    }
}
