<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCourseInstructionLevelsTable extends Migration
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
        Schema::create('CourseInstructionLevels', function (Blueprint $table) {
            $table->increments('ID');
            $table->string('Type')->default('');
            $table->integer('CourseID')->default(0);
            $table->integer('CredentialID')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('CourseInstructionLevels');
    }
}
