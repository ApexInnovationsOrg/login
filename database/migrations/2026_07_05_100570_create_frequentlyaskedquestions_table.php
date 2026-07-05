<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFrequentlyAskedQuestionsTable extends Migration
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
        Schema::create('FrequentlyAskedQuestions', function (Blueprint $table) {
            $table->increments('ID');
            $table->string('Title')->default('');
            $table->text('Content')->nullable(); // NOT NULL in prod (implicit '' when omitted, non-strict MySQL)
            $table->integer('ProductID')->nullable();
            $table->string('Type')->default('');
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
        Schema::dropIfExists('FrequentlyAskedQuestions');
    }
}
