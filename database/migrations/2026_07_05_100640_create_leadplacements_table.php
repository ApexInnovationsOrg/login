<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLeadPlacementsTable extends Migration
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
        Schema::create('LeadPlacements', function (Blueprint $table) {
            $table->increments('ID');
            $table->integer('TestResultID')->default(0);
            $table->integer('QuestionID')->default(0);
            $table->string('Placements')->default('');
            $table->enum('Passed', ['Y', 'N']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('LeadPlacements');
    }
}
