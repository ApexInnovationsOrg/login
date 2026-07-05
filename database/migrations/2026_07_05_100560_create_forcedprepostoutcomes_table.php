<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateForcedPrePostOutcomesTable extends Migration
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
        Schema::create('ForcedPrePostOutcomes', function (Blueprint $table) {
            $table->increments('ID');
            $table->integer('CourseID')->default(0);
            $table->integer('TestQuestionID')->default(0);
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
        Schema::dropIfExists('ForcedPrePostOutcomes');
    }
}
