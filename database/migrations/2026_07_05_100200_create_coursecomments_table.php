<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCourseCommentsTable extends Migration
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
        Schema::create('CourseComments', function (Blueprint $table) {
            $table->increments('ID');
            $table->integer('UserID')->default(0);
            $table->integer('CourseID')->default(0);
            $table->dateTime('Submitted')->nullable(); // NOT NULL in prod (zero-date when omitted, non-strict MySQL)
            $table->string('PageName')->default('');
            $table->string('PageURL')->default('');
            $table->text('Comment')->nullable(); // NOT NULL in prod (implicit '' when omitted, non-strict MySQL)
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('CourseComments');
    }
}
