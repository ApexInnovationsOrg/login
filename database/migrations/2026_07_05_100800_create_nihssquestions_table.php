<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNIHSSQuestionsTable extends Migration
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
        Schema::create('NIHSSQuestions', function (Blueprint $table) {
            $table->increments('ID');
            $table->string('Label')->default('');
            $table->string('Name')->default('');
            $table->text('Description')->nullable(); // NOT NULL in prod (implicit '' when omitted, non-strict MySQL)
            $table->enum('HasVideo', ['Y', 'N'])->default('Y');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('NIHSSQuestions');
    }
}
