<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNIHAnswersTable extends Migration
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
        Schema::create('NIHAnswers', function (Blueprint $table) {
            $table->increments('ID');
            $table->integer('NIHQuestionID')->default(0);
            $table->string('Answer')->default('');
            $table->integer('Value')->nullable();
            $table->enum('Correct', ['Y', 'N'])->nullable();
            $table->string('Explanation')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('NIHAnswers');
    }
}
