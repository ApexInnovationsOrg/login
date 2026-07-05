<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNIHSSAnswersTable extends Migration
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
        Schema::create('NIHSSAnswers', function (Blueprint $table) {
            $table->increments('ID');
            $table->integer('QuestionID')->default(0);
            $table->string('Answer')->default('');
            $table->integer('Value')->nullable();
            $table->string('CorrectFor')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('NIHSSAnswers');
    }
}
